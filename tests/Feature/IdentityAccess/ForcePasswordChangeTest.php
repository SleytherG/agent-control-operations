<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForcePasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $operator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->org = Organization::factory()->create();
        $this->operator = User::factory()->create([
            'organization_id' => $this->org->id,
            'username_normalized' => 'operator1',
            'password' => Hash::make('password123'),
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => null,
        ]);
    }

    public function test_operator_with_null_password_changed_at_is_redirected_to_change_password(): void
    {
        $this->actingAsJwt($this->operator);

        $response = $this->get(route('home'));
        $response->assertRedirect(route('password.change'));
    }

    public function test_operator_can_change_password(): void
    {
        $this->actingAsJwt($this->operator);

        $response = $this->patch(route('password.change.update'), [
            'current_password' => 'password123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('status');

        $this->assertNotNull($this->operator->fresh()->password_changed_at);
    }

    public function test_operator_cannot_change_password_with_wrong_current(): void
    {
        $this->actingAsJwt($this->operator);

        $response = $this->patch(route('password.change.update'), [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertNull($this->operator->fresh()->password_changed_at);
    }

    public function test_admin_with_password_changed_set_is_not_redirected(): void
    {
        $admin = User::factory()->create([
            'organization_id' => $this->org->id,
            'role' => Role::ADMINISTRADOR_PROPIETARIO,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);
        $this->actingAsJwt($admin);

        // Admin should not be redirected to password change page
        $response = $this->get(route('password.change'));
        $response->assertStatus(200);
    }
}
