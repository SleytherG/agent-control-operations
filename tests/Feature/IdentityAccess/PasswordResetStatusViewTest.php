<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Domain\Enums\PasswordResetStatus;
use App\Modules\IdentityAccess\Models\PasswordReset;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordResetStatusViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_list_shows_latest_reset_status_without_secret(): void
    {
        $admin = User::factory()->administradorPropietario()->create();
        $operator = User::factory()->create(['organization_id' => $admin->organization_id]);
        PasswordReset::factory()->create([
            'organization_id' => $operator->organization_id,
            'user_id' => $operator->id,
            'initiated_by_user_id' => $admin->id,
            'status' => PasswordResetStatus::PENDING,
        ]);
        $this->actingAsJwt($admin);

        $this->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Pendiente')
            ->assertSee(route('admin.users.password-resets.index', $operator))
            ->assertDontSee('temporaryPassword');
    }
}
