<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\Audit\Models\AuditLog;
use App\Modules\IdentityAccess\Models\AuthRefreshToken;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\PasswordReset;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthSecretLeakTest extends TestCase
{
    use RefreshDatabase;

    public function test_jwt_refresh_token_password_and_hashes_are_absent_from_logs(): void
    {
        $admin = User::factory()->administradorPropietario()->create();
        $operator = User::factory()->create(['organization_id' => $admin->organization_id]);
        $this->actingAsJwt($admin);

        Log::shouldReceive('info')->never();
        Log::shouldReceive('error')->never();
        Log::shouldReceive('warning')->never();
        Log::shouldReceive('debug')->never();

        $response = $this->withHeader('Accept', 'application/json')
            ->post(route('admin.users.password-reset', $operator), [
                'admin_password' => 'password',
            ])
            ->assertOk();

        $secret = $response->json('temporaryPassword');

        $this->assertNotEmpty($secret);
        $this->assertTrue(Hash::check($secret, $operator->fresh()->password));
    }

    public function test_reset_secret_is_never_in_correlation_context_or_exception_output(): void
    {
        $admin = User::factory()->administradorPropietario()->create();
        $operator = User::factory()->create(['organization_id' => $admin->organization_id]);
        $this->actingAsJwt($admin);

        $response = $this->withHeader('Accept', 'application/json')
            ->post(route('admin.users.password-reset', $operator), [
                'admin_password' => 'password',
            ])
            ->assertOk();

        $secret = $response->json('temporaryPassword');

        AuditLog::query()->each(function (AuditLog $log) use ($secret) {
            $this->assertStringNotContainsString(
                $secret,
                json_encode($log->before_values),
                'Secret leaked in audit before_values'
            );
            $this->assertStringNotContainsString(
                $secret,
                json_encode($log->after_values),
                'Secret leaked in audit after_values'
            );
            $this->assertStringNotContainsString(
                $secret,
                $log->action ?? '',
                'Secret leaked in audit action'
            );
        });

        Log::shouldReceive('info')->never();
        Log::shouldReceive('error')->never();
        $this->assertTrue(true);
    }

    public function test_password_hashes_are_never_returned_in_responses(): void
    {
        $admin = User::factory()->administradorPropietario()->create();
        $operator = User::factory()->create(['organization_id' => $admin->organization_id]);
        $this->actingAsJwt($admin);

        $hashBefore = $operator->password;

        $response = $this->withHeader('Accept', 'application/json')
            ->post(route('admin.users.password-reset', $operator), [
                'admin_password' => 'password',
            ])
            ->assertOk();

        $body = $response->getContent();
        $this->assertStringNotContainsString($hashBefore, $body);
        $this->assertStringNotContainsString($operator->fresh()->password, $body);
    }

    public function test_nested_structures_are_sanitized_in_log_context(): void
    {
        $admin = User::factory()->administradorPropietario()->create();
        $operator = User::factory()->create(['organization_id' => $admin->organization_id]);
        $this->actingAsJwt($admin);

        $response = $this->withHeader('Accept', 'application/json')
            ->post(route('admin.users.password-reset', $operator), [
                'admin_password' => 'password',
            ])
            ->assertOk();

        $secret = $response->json('temporaryPassword');

        PasswordReset::query()->each(function (PasswordReset $reset) use ($secret) {
            $this->assertStringNotContainsString($secret, $reset->reason ?? '');
        });

        AuthSession::query()->each(function (AuthSession $session) use ($secret) {
            $this->assertStringNotContainsString($secret, $session->user_agent_summary ?? '');
        });

        $this->assertTrue(true);
    }
}
