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

class BankAgentDeactivateTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $admin;
    private BankAgent $agent;

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
        $this->agent = BankAgent::create([
            'organization_id' => $this->org->id, 'store_id' => $store->id,
            'bank_id' => $bank->id, 'code' => 'AG-001', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_deactivate_agent_terminates_active_assignments(): void
    {
        $operator = User::factory()->create([
            'organization_id' => $this->org->id,
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);

        $assignment = UserBankAgentAssignment::create([
            'user_id' => $operator->id,
            'bank_agent_id' => $this->agent->id,
            'assigned_by' => $this->admin->id,
            'assigned_at' => now(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->admin);

        $response = $this->delete(route('admin.bank-agents.deactivate', $this->agent));

        $response->assertRedirect(route('admin.bank-agents.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('bank_agents', [
            'id' => $this->agent->id,
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('user_bank_agent_assignments', [
            'id' => $assignment->id,
            'is_active' => false,
        ]);

        $this->assertNotNull($assignment->fresh()->unassigned_at);
        $this->assertNotNull($this->agent->fresh()->deactivated_at);
    }

    public function test_operator_cannot_deactivate_agent(): void
    {
        $operator = User::factory()->create([
            'organization_id' => $this->org->id,
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);
        $this->actingAsJwt($operator);

        $response = $this->delete(route('admin.bank-agents.deactivate', $this->agent));
        $response->assertForbidden();
    }
}
