<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperatorRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $admin;

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
    }

    public function test_admin_can_create_operator(): void
    {
        $this->actingAsJwt($this->admin);

        $response = $this->post(route('admin.users.store'), [
            'username' => 'operator1',
            'email' => 'operator1@test.local',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'username_normalized' => 'operator1',
            'email_normalized' => 'operator1@test.local',
            'role' => 'OPERADOR',
            'status' => 'ACTIVE',
        ]);

        $operator = User::where('username_normalized', 'operator1')->first();
        $this->assertNull($operator->password_changed_at);
    }

    public function test_operator_password_changed_at_is_null(): void
    {
        $this->actingAsJwt($this->admin);

        $this->post(route('admin.users.store'), [
            'username' => 'operator2',
            'email' => 'operator2@test.local',
            'password' => 'password123',
        ]);

        $operator = User::where('username_normalized', 'operator2')->first();
        $this->assertNull($operator->password_changed_at);
        $this->assertEquals(Role::OPERADOR, $operator->role);
    }

    public function test_operator_cannot_create_operator(): void
    {
        $operator = User::factory()->create([
            'organization_id' => $this->org->id,
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);
        $this->actingAsJwt($operator);

        $response = $this->post(route('admin.users.store'), [
            'username' => 'hacker',
            'email' => 'hacker@test.local',
            'password' => 'password123',
        ]);

        $response->assertForbidden();
    }

    public function test_operator_username_must_be_unique_per_org(): void
    {
        $this->actingAsJwt($this->admin);

        User::factory()->create([
            'organization_id' => $this->org->id,
            'username_normalized' => 'operator1',
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE,
        ]);

        $response = $this->post(route('admin.users.store'), [
            'username' => 'operator1',
            'email' => 'other@test.local',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('username');
    }
}
