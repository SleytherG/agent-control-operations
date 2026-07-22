<?php

namespace Tests\Feature\BankingNetwork;

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

class OperatorAgentIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_cannot_see_other_operators_agents(): void
    {
        $org = Organization::factory()->create();

        $admin = User::factory()->create([
            'organization_id' => $org->id,
            'role' => Role::ADMINISTRADOR_PROPIETARIO,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);

        $operator1 = User::factory()->create([
            'organization_id' => $org->id,
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);

        $operator2 = User::factory()->create([
            'organization_id' => $org->id,
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);

        $region = Region::create([
            'organization_id' => $org->id, 'name' => 'Lima', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $province = Province::create([
            'organization_id' => $org->id, 'region_id' => $region->id, 'name' => 'Lima',
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $district = District::create([
            'organization_id' => $org->id, 'province_id' => $province->id, 'name' => 'Miraflores',
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $store = Store::create([
            'organization_id' => $org->id, 'district_id' => $district->id,
            'code' => 'ST-001', 'name' => 'Tienda Test', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $bank = Bank::create([
            'organization_id' => $org->id, 'code' => 'BCP', 'name' => 'BCP',
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $agent1 = BankAgent::create([
            'organization_id' => $org->id, 'store_id' => $store->id,
            'bank_id' => $bank->id, 'code' => 'AG-001', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $agent2 = BankAgent::create([
            'organization_id' => $org->id, 'store_id' => $store->id,
            'bank_id' => $bank->id, 'code' => 'AG-002', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        UserBankAgentAssignment::create([
            'user_id' => $operator1->id,
            'bank_agent_id' => $agent1->id,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        UserBankAgentAssignment::create([
            'user_id' => $operator2->id,
            'bank_agent_id' => $agent2->id,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAsJwt($operator1);

        $response = $this->get(route('my-agents.index'));
        $response->assertStatus(200);
        $response->assertSee('AG-001');
        $response->assertDontSee('AG-002');
    }
}
