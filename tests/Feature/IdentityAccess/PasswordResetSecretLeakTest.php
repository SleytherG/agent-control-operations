<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\Audit\Models\AuditLog;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetSecretLeakTest extends TestCase
{
    use RefreshDatabase;

    public function test_secret_is_returned_once_with_no_store_and_is_not_persisted_in_plaintext(): void
    {
        $admin = User::factory()->administradorPropietario()->create();
        $operator = User::factory()->create(['organization_id' => $admin->organization_id]);
        $this->actingAsJwt($admin);

        $response = $this->withHeader('Accept', 'application/json')->post(route('admin.users.password-reset', $operator), [
            'admin_password' => 'password',
        ])->assertOk()->assertHeader('Cache-Control', 'must-revalidate, no-cache, no-store, private');

        $secret = $response->json('temporaryPassword');
        $this->assertTrue(Hash::check($secret, $operator->fresh()->password));
        $this->assertDatabaseMissing('password_resets', ['reason' => $secret]);
        $this->assertFalse(str_contains(AuditLog::query()->get()->toJson(), $secret));

        $this->get(route('admin.users.edit', $operator))
            ->assertOk()
            ->assertDontSee($secret);
    }
}
