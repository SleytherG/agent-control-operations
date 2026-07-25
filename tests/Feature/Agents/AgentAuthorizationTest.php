<?php

namespace Tests\Feature\Agents;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Agents\Models\Agent;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private Organization $otherOrg;
    private User $admin;
    private User $operator;
    private User $otherAdmin;
    private Agent $agent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->org = Organization::factory()->create();
        $this->otherOrg = Organization::factory()->create();

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

        $this->otherAdmin = User::factory()->create([
            'organization_id' => $this->otherOrg->id,
            'role' => Role::ADMINISTRADOR_PROPIETARIO,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);

        $this->agent = Agent::create([
            'organization_id' => $this->org->id,
            'code' => 'AG-AUTH',
            'name' => 'Agent Auth',
            'city' => 'Lima',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_admin_can_view_agents_index(): void
    {
        $this->actingAsJwt($this->admin);
        $this->get(route('admin.agents.index'))->assertOk();
    }

    public function test_operator_cannot_view_agents_index(): void
    {
        $this->actingAsJwt($this->operator);
        $this->get(route('admin.agents.index'))->assertForbidden();
    }

    public function test_admin_can_create_agent(): void
    {
        $this->actingAsJwt($this->admin);
        $this->get(route('admin.agents.create'))->assertOk();
    }

    public function test_operator_cannot_create_agent(): void
    {
        $this->actingAsJwt($this->operator);
        $this->get(route('admin.agents.create'))->assertForbidden();
    }

    public function test_admin_can_update_own_agent(): void
    {
        $this->actingAsJwt($this->admin);
        $this->get(route('admin.agents.edit', $this->agent))->assertOk();
    }

    public function test_admin_cannot_update_other_org_agent(): void
    {
        $this->actingAsJwt($this->otherAdmin);
        $this->get(route('admin.agents.edit', $this->agent))->assertForbidden();
    }

    public function test_operator_cannot_update_agent(): void
    {
        $this->actingAsJwt($this->operator);
        $this->get(route('admin.agents.edit', $this->agent))->assertForbidden();
    }

    public function test_admin_can_deactivate_own_agent(): void
    {
        $this->actingAsJwt($this->admin);
        $this->delete(route('admin.agents.deactivate', $this->agent));

        $this->assertFalse($this->agent->fresh()->is_active);
    }

    public function test_admin_cannot_deactivate_other_org_agent(): void
    {
        $this->actingAsJwt($this->otherAdmin);
        $this->delete(route('admin.agents.deactivate', $this->agent))->assertForbidden();
    }
}
