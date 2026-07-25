<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPasswordReauthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_wrong_admin_password_does_not_mutate_target(): void
    {
        $admin = User::factory()->administradorPropietario()->create();
        $operator = User::factory()->create(['organization_id' => $admin->organization_id]);
        $hash = $operator->password;
        $this->actingAsJwt($admin);

        $this->withHeader('Accept', 'application/json')->post(route('admin.users.password-reset', $operator), [
            'admin_password' => 'wrong-password',
        ])->assertUnprocessable()->assertJsonValidationErrors('admin_password');

        $this->assertSame($hash, $operator->fresh()->password);
        $this->assertDatabaseMissing('password_resets', ['user_id' => $operator->id]);
    }

    public function test_step_up_is_throttled_after_five_failures(): void
    {
        config()->set('session-security.password_reset.step_up_max_attempts', 5);
        $admin = User::factory()->administradorPropietario()->create();
        $operator = User::factory()->create(['organization_id' => $admin->organization_id]);
        $this->actingAsJwt($admin);

        foreach (range(1, 5) as $attempt) {
            $this->withHeader('Accept', 'application/json')->post(route('admin.users.password-reset', $operator), [
                'admin_password' => 'wrong-password',
            ])->assertUnprocessable();
        }

        $this->withHeader('Accept', 'application/json')->post(route('admin.users.password-reset', $operator), [
            'admin_password' => 'password',
        ])->assertStatus(429);
    }
}
