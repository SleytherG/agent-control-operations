<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperatorDeactivationTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $admin;
    private User $operator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->org = Organization::factory()->create();
        $this->admin = User::factory()->create([
            'organization_id' => $this->org->id,
            'role' => Role::ADMINISTRADOR_PROPIETARIO,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);
        $this->operator = User::factory()->create([
            'organization_id' => $this->org->id,
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);
    }

    public function test_admin_can_deactivate_operator(): void
    {
        $this->actingAsJwt($this->admin);

        $response = $this->delete(route('admin.users.deactivate-operator', $this->operator));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'id' => $this->operator->id,
            'status' => 'INACTIVE',
        ]);

        $this->assertNotNull($this->operator->fresh()->deactivated_at);
    }

    public function test_admin_cannot_deactivate_self(): void
    {
        $this->actingAsJwt($this->admin);

        $response = $this->delete(route('admin.users.deactivate-operator', $this->admin));

        $response->assertForbidden();
    }

    public function test_operator_cannot_deactivate_anyone(): void
    {
        $otherOperator = User::factory()->create([
            'organization_id' => $this->org->id,
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);
        $this->actingAsJwt($this->operator);

        $response = $this->delete(route('admin.users.deactivate-operator', $otherOperator));
        $response->assertForbidden();
    }
}
