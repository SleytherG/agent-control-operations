<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Domain\Enums\PasswordResetStatus;
use App\Modules\IdentityAccess\Domain\Enums\RefreshTokenState;
use App\Modules\IdentityAccess\Models\AuthRefreshToken;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\PasswordReset;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\IdentityAccess\Services\RefreshTokenService;
use Tests\TestCase;

class RestrictedSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_restricted_session_can_only_change_password_or_logout(): void
    {
        [$operator, $session, $reset] = $this->restrictedSession();
        $this->actingAsJwtSession($operator, $session);

        $this->get(route('home'))->assertRedirect(route('password.change'));
        $this->get(route('password.change'))->assertOk();
        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertSame(PasswordResetStatus::CONSUMED, $reset->fresh()->status);
    }

    public function test_linked_reset_remains_consumed_until_password_change(): void
    {
        [$operator, $session, $reset] = $this->restrictedSession();
        $this->actingAsJwtSession($operator, $session);

        $this->get(route('home'))->assertRedirect(route('password.change'));

        $this->assertSame(PasswordResetStatus::CONSUMED, $reset->fresh()->status);
        $this->assertSame($reset->id, $session->fresh()->password_reset_id);
    }

    public function test_refresh_rotates_credentials_without_removing_restriction(): void
    {
        [$operator, $session, $reset] = $this->restrictedSession();
        $refreshService = app(RefreshTokenService::class);
        $raw = $refreshService->generate();
        AuthRefreshToken::factory()->create([
            'auth_session_id' => $session->id,
            'token_hash' => $refreshService->hash($raw),
            'state' => RefreshTokenState::ACTIVE,
        ]);

        $this->withCredentials()
            ->withCookie(config('session-security.cookies.refresh_name'), $raw)
            ->postJson(route('auth.refresh'))
            ->assertOk();

        $this->assertSame($reset->id, $session->fresh()->password_reset_id);
        $this->actingAsJwtSession($operator, $session->fresh());
        $this->get(route('home'))->assertRedirect(route('password.change'));
    }

    private function restrictedSession(): array
    {
        $admin = User::factory()->administradorPropietario()->create();
        $operator = User::factory()->create(['organization_id' => $admin->organization_id]);
        $reset = PasswordReset::factory()->create([
            'organization_id' => $operator->organization_id,
            'user_id' => $operator->id,
            'initiated_by_user_id' => $admin->id,
            'status' => PasswordResetStatus::CONSUMED,
            'consumed_at' => now(),
        ]);
        $session = AuthSession::factory()->create([
            'user_id' => $operator->id,
            'password_reset_id' => $reset->id,
        ]);

        return [$operator, $session, $reset];
    }
}
