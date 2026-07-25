<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Application\Actions\ResetOperatorPassword;
use App\Modules\IdentityAccess\Domain\Enums\PasswordResetStatus;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\PasswordReset;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemporaryPasswordLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_temporary_login_is_consumed_and_second_is_rejected(): void
    {
        [$operator, $temporary] = $this->issueTemporaryPassword();

        $this->post(route('login.store'), [
            'identifier' => $operator->username_normalized,
            'password' => $temporary,
        ])->assertRedirect(route('password.change'));

        $reset = PasswordReset::where('user_id', $operator->id)->sole();
        $this->assertSame(PasswordResetStatus::CONSUMED, $reset->status);
        $this->assertDatabaseHas('auth_sessions', [
            'user_id' => $operator->id,
            'password_reset_id' => $reset->id,
        ]);

        $this->post(route('login.store'), [
            'identifier' => $operator->username_normalized,
            'password' => $temporary,
        ])->assertRedirect(route('login'))->assertSessionHas('login_state', 'error');

        $this->assertSame(1, AuthSession::where('password_reset_id', $reset->id)->count());
    }

    public function test_incorrect_or_expired_temporary_password_uses_generic_failure(): void
    {
        [$operator, $temporary] = $this->issueTemporaryPassword();
        PasswordReset::where('user_id', $operator->id)->update(['expires_at' => now()->subSecond()]);

        $this->post(route('login.store'), [
            'identifier' => $operator->username_normalized,
            'password' => $temporary,
        ])->assertRedirect(route('login'))->assertSessionHas('login_state', 'error');

        $this->assertSame(
            PasswordResetStatus::EXPIRED,
            PasswordReset::where('user_id', $operator->id)->sole()->status,
        );
    }

    private function issueTemporaryPassword(): array
    {
        $admin = User::factory()->administradorPropietario()->create();
        $operator = User::factory()->create(['organization_id' => $admin->organization_id]);
        $result = app(ResetOperatorPassword::class)->execute($operator, $admin, 'password');

        return [$operator->fresh(), $result['temporary_password']];
    }
}
