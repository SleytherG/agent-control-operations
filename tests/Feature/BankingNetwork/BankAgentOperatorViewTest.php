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

class BankAgentOperatorViewTest extends TestCase
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
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);
    }

    public function test_operator_cannot_access_admin_bank_agent_management(): void
    {
        $this->actingAsJwt($this->operator);

        $response = $this->get(route('admin.bank-agents.index'));
        $response->assertForbidden();

        $response = $this->get(route('admin.bank-agents.create'));
        $response->assertForbidden();
    }

    public function test_operator_sees_only_assigned_active_agents_in_my_agents(): void
    {
        $admin = User::factory()->create([
            'organization_id' => $this->org->id,
            'role' => Role::ADMINISTRADOR_PROPIETARIO,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);

        $region = Region::create([
            'organization_id' => $this->org->id, 'name' => 'Lima', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $province = Province::create([
            'organization_id' => $this->org->id, 'region_id' => $region->id, 'name' => 'Lima',
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $district = District::create([
            'organization_id' => $this->org->id, 'province_id' => $province->id, 'name' => 'Miraflores',
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $store = Store::create([
            'organization_id' => $this->org->id, 'district_id' => $district->id,
            'code' => 'ST-001', 'name' => 'Tienda Test', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $bank = Bank::create([
            'organization_id' => $this->org->id, 'code' => 'BCP', 'name' => 'BCP',
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $assignedAgent = BankAgent::create([
            'organization_id' => $this->org->id, 'store_id' => $store->id,
            'bank_id' => $bank->id, 'code' => 'AG-ASSIGNED', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $unassignedAgent = BankAgent::create([
            'organization_id' => $this->org->id, 'store_id' => $store->id,
            'bank_id' => $bank->id, 'code' => 'AG-UNASSIGNED', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        UserBankAgentAssignment::create([
            'user_id' => $this->operator->id,
            'bank_agent_id' => $assignedAgent->id,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->operator);

        $response = $this->get(route('my-agents.index'));
        $response->assertStatus(200);
        $response->assertSee('AG-ASSIGNED');
        $response->assertDontSee('AG-UNASSIGNED');
    }
}
