<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Application\Actions\ResetOperatorPassword;
use App\Modules\IdentityAccess\Domain\Enums\PasswordResetStatus;
use App\Modules\IdentityAccess\Models\PasswordReset;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TemporaryPasswordExpiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_temporary_password_succeeds_before_boundary_and_fails_at_boundary(): void
    {
        Carbon::setTestNow('2026-07-23 10:00:00');
        [$operator, $temporary] = $this->issue();
        Carbon::setTestNow('2026-07-23 10:59:59');

        $this->post(route('login.store'), [
            'identifier' => $operator->username_normalized,
            'password' => $temporary,
        ])->assertRedirect(route('password.change'));

        Carbon::setTestNow('2026-07-23 12:00:00');
        [$other, $otherTemporary] = $this->issue();
        Carbon::setTestNow('2026-07-23 13:00:00');

        $this->post(route('login.store'), [
            'identifier' => $other->username_normalized,
            'password' => $otherTemporary,
        ])->assertRedirect(route('login'));

        $this->assertSame(
            PasswordResetStatus::EXPIRED,
            PasswordReset::where('user_id', $other->id)->sole()->status,
        );
        Carbon::setTestNow();
    }

    private function issue(): array
    {
        $admin = User::factory()->administradorPropietario()->create();
        $operator = User::factory()->create(['organization_id' => $admin->organization_id]);
        $result = app(ResetOperatorPassword::class)->execute($operator, $admin, 'password');

        return [$operator->fresh(), $result['temporary_password']];
    }
}
