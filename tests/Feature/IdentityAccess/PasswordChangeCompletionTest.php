<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Domain\Enums\PasswordResetStatus;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\PasswordReset;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordChangeCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_restricted_form_does_not_request_temporary_password_and_completes_cycle(): void
    {
        [$operator, $session, $reset] = $this->restrictedSession('Temporary!23456789');
        $this->actingAsJwtSession($operator, $session);

        $this->get(route('password.change'))
            ->assertOk()
            ->assertDontSee('current_password')
            ->assertSee('password_confirmation');

        $this->patch(route('password.change.update'), [
            'password' => 'Definitive!987',
            'password_confirmation' => 'Definitive!987',
        ])->assertRedirect(route('dashboard.operator'))->assertSessionHas('status');

        $this->assertSame(PasswordResetStatus::COMPLETED, $reset->fresh()->status);
        $this->assertTrue(Hash::check('Definitive!987', $operator->fresh()->password));
        $this->assertNotNull($operator->fresh()->password_changed_at);
    }

    public function test_new_password_cannot_equal_temporary_hash(): void
    {
        [$operator, $session, $reset] = $this->restrictedSession('Temporary!23456789');
        $this->actingAsJwtSession($operator, $session);

        $this->patch(route('password.change.update'), [
            'password' => 'Temporary!23456789',
            'password_confirmation' => 'Temporary!23456789',
        ])->assertSessionHasErrors('password');

        $this->assertSame(PasswordResetStatus::CONSUMED, $reset->fresh()->status);
    }

    private function restrictedSession(string $temporary): array
    {
        $admin = User::factory()->administradorPropietario()->create();
        $operator = User::factory()->create([
            'organization_id' => $admin->organization_id,
            'password' => Hash::make($temporary),
            'password_changed_at' => null,
        ]);
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
