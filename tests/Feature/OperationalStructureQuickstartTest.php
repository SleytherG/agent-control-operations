<?php

namespace Tests\Feature;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\District;
use App\Modules\Organization\Models\Province;
use App\Modules\Organization\Models\Region;
use App\Modules\Organization\Models\Organization;
use App\Modules\Organization\Models\Store;
use App\Modules\BankingNetwork\Models\Bank;
use App\Modules\BankingNetwork\Models\BankAgent;
use App\Modules\BankingNetwork\Models\UserBankAgentAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalStructureQuickstartTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_operational_workflow(): void
    {
        $org = Organization::factory()->create();

        $admin = User::factory()->create([
            'organization_id' => $org->id,
            'role' => Role::ADMINISTRADOR_PROPIETARIO,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);

        $this->actingAsJwt($admin);

        // Create geographical hierarchy
        $this->post(route('admin.regions.store'), ['name' => 'Lima']);
        $region = Region::first();

        $this->post(route('admin.regions.provinces.store', $region), ['name' => 'Lima']);
        $province = Province::first();

        $this->post(route('admin.provinces.districts.store', $province), ['name' => 'Miraflores']);
        $district = District::first();

        // Create a bank
        $this->post(route('admin.banks.store'), ['code' => 'BCP', 'name' => 'Banco de Crédito']);
        $bank = Bank::first();

        // Create a store
        $this->post(route('admin.stores.store'), [
            'district_id' => $district->id,
            'code' => 'ST-001',
            'name' => 'Tienda Miraflores',
            'address' => 'Av. Larco 123',
        ]);
        $store = Store::first();

        // Create a bank agent
        $this->post(route('admin.bank-agents.store'), [
            'store_id' => $store->id,
            'bank_id' => $bank->id,
            'code' => 'AG-001',
        ]);
        $agent = BankAgent::first();

        // Create an operator
        $this->post(route('admin.users.store'), [
            'username' => 'operador1',
            'email' => 'operador1@test.local',
            'password' => 'password123',
        ]);
        $operator = User::where('username_normalized', 'operador1')->first();
        $this->assertNotNull($operator);
        $this->assertEquals(Role::OPERADOR, $operator->role);
        $this->assertNull($operator->password_changed_at);

        // Assign operator to agent
        $this->post(route('admin.users.assignments.store', $operator), [
            'bank_agent_id' => $agent->id,
        ]);

        $this->assertDatabaseHas('user_bank_agent_assignments', [
            'user_id' => $operator->id,
            'bank_agent_id' => $agent->id,
            'is_active' => true,
        ]);

        // Verify operator can see their agents (after password change)
        $operator->update(['password_changed_at' => now()]);
        $this->actingAsJwt($operator);
        $response = $this->get(route('my-agents.index'));
        $response->assertStatus(200);
        $response->assertSee('AG-001');

        // Force password change redirect (operator with null password_changed_at)
        $operator->update(['password_changed_at' => null]);
        $response = $this->get(route('password.change'));
        $response->assertStatus(200);
    }

    public function test_deactivation_chain_works(): void
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create([
            'organization_id' => $org->id,
            'role' => Role::ADMINISTRADOR_PROPIETARIO,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);
        $this->actingAsJwt($admin);

        // Setup
        $region = Region::create(['organization_id' => $org->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $province = Province::create(['organization_id' => $org->id, 'region_id' => $region->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $district = District::create(['organization_id' => $org->id, 'province_id' => $province->id, 'name' => 'Miraflores', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $store = Store::create(['organization_id' => $org->id, 'district_id' => $district->id, 'code' => 'ST-001', 'name' => 'Test', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $bank = Bank::create(['organization_id' => $org->id, 'code' => 'BCP', 'name' => 'BCP', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $agent = BankAgent::create(['organization_id' => $org->id, 'store_id' => $store->id, 'bank_id' => $bank->id, 'code' => 'AG-001', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $operator = User::factory()->create([
            'organization_id' => $org->id, 'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE, 'password_changed_at' => now(),
        ]);

        UserBankAgentAssignment::create([
            'user_id' => $operator->id, 'bank_agent_id' => $agent->id,
            'assigned_by' => $admin->id, 'assigned_at' => now(),
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);

        // Deactivate agent - should terminate assignments
        $this->delete(route('admin.bank-agents.deactivate', $agent));
        $this->assertDatabaseHas('bank_agents', ['id' => $agent->id, 'is_active' => false]);
        $this->assertDatabaseMissing('user_bank_agent_assignments', ['bank_agent_id' => $agent->id, 'is_active' => true]);

        // Agent is now inactive, so store can be deactivated
        $this->delete(route('admin.stores.deactivate', $store));
        $this->assertDatabaseHas('stores', ['id' => $store->id, 'is_active' => false]);

        // Deactivate operator
        $this->delete(route('admin.users.deactivate-operator', $operator));
        $this->assertDatabaseHas('users', ['id' => $operator->id, 'status' => 'INACTIVE']);
    }
}
