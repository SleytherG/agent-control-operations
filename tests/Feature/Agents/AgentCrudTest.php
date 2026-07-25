<?php

namespace Tests\Feature\Agents;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Agents\Models\Agent;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentCrudTest extends TestCase
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

    public function test_admin_can_create_agent_with_valid_data(): void
    {
        $this->actingAsJwt($this->admin);
        $this->post(route('admin.agents.store'), [
            'code' => 'AG-001',
            'name' => 'Agente Centro',
            'city' => 'Lima',
            'region' => 'Lima',
        ])->assertRedirect(route('admin.agents.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('agents', [
            'code' => 'AG-001',
            'name' => 'Agente Centro',
            'city' => 'Lima',
            'is_active' => true,
        ]);
    }

    public function test_admin_cannot_create_agent_with_duplicate_code_in_same_org(): void
    {
        Agent::create([
            'organization_id' => $this->org->id,
            'code' => 'AG-DUP',
            'name' => 'Original',
            'city' => 'Lima',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->admin);
        $this->post(route('admin.agents.store'), [
            'code' => 'AG-DUP',
            'name' => 'Duplicado',
            'city' => 'Lima',
        ])->assertSessionHasErrors(['code']);
    }

    public function test_admin_can_update_agent(): void
    {
        $agent = Agent::create([
            'organization_id' => $this->org->id,
            'code' => 'AG-UPD',
            'name' => 'Original',
            'city' => 'Lima',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->admin);
        $this->patch(route('admin.agents.update', $agent), [
            'code' => 'AG-UPD',
            'name' => 'Actualizado',
            'city' => 'Arequipa',
        ])->assertRedirect(route('admin.agents.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('agents', [
            'id' => $agent->id,
            'name' => 'Actualizado',
            'city' => 'Arequipa',
        ]);
    }

    public function test_admin_can_deactivate_agent(): void
    {
        $agent = Agent::create([
            'organization_id' => $this->org->id,
            'code' => 'AG-DEACT',
            'name' => 'To Deactivate',
            'city' => 'Lima',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->admin);
        $this->delete(route('admin.agents.deactivate', $agent))
            ->assertRedirect(route('admin.agents.index'))
            ->assertSessionHas('status');

        $this->assertFalse($agent->fresh()->is_active);
        $this->assertNotNull($agent->fresh()->deactivated_at);
    }

    public function test_create_agent_requires_required_fields(): void
    {
        $this->actingAsJwt($this->admin);
        $this->post(route('admin.agents.store'), [])
            ->assertSessionHasErrors(['code', 'name', 'city']);
    }

    public function test_create_agent_with_optional_fields(): void
    {
        $this->actingAsJwt($this->admin);
        $this->post(route('admin.agents.store'), [
            'code' => 'AG-OPT',
            'name' => 'Agent with Optionals',
            'city' => 'Lima',
            'region' => 'Lima',
            'province' => 'Lima',
            'district' => 'Miraflores',
            'address' => 'Av. Arequipa 123',
            'description' => 'Descripción del agente',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('agents', [
            'code' => 'AG-OPT',
            'address' => 'Av. Arequipa 123',
            'description' => 'Descripción del agente',
        ]);
    }

    public function test_agent_index_is_paginated(): void
    {
        for ($i = 1; $i <= 30; $i++) {
            Agent::create([
                'organization_id' => $this->org->id,
                'code' => 'AG-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'name' => 'Agent ' . $i,
                'city' => 'Lima',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->actingAsJwt($this->admin);
        $response = $this->get(route('admin.agents.index'));
        $response->assertOk();

        $agents = $response->viewData('agents');
        $this->assertNotNull($agents);
        $this->assertCount(20, $agents);
        $this->assertEquals(30, $agents->total());
    }

    public function test_agent_index_can_filter_by_city(): void
    {
        Agent::create([
            'organization_id' => $this->org->id,
            'code' => 'AG-LIM',
            'name' => 'Agent Lima',
            'city' => 'Lima',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Agent::create([
            'organization_id' => $this->org->id,
            'code' => 'AG-ARE',
            'name' => 'Agent Arequipa',
            'city' => 'Arequipa',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->admin);
        $response = $this->get(route('admin.agents.index', ['city' => 'Arequipa']));
        $response->assertOk();

        $agents = $response->viewData('agents');
        $this->assertNotNull($agents);
        $this->assertCount(1, $agents);
        $this->assertEquals('Arequipa', $agents->first()->city);
    }
}
