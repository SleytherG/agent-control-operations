<?php

namespace Tests\Feature\Reporting;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use App\Modules\Organization\Models\Store;
use App\Modules\Organization\Models\District;
use App\Modules\Organization\Models\Province;
use App\Modules\Organization\Models\Region;
use App\Modules\BankingNetwork\Models\Bank;
use App\Modules\BankingNetwork\Models\BankAgent;
use App\Modules\BankingNetwork\Models\UserBankAgentAssignment;
use App\Modules\Operations\Models\OperationType;
use App\Modules\Operations\Models\Operation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminDashboardFilterConsistencyTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $admin;
    private Store $store1;
    private Store $store2;
    private BankAgent $agent1;
    private BankAgent $agent2;
    private OperationType $type;

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

        $region = Region::create(['organization_id' => $this->org->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $province = Province::create(['organization_id' => $this->org->id, 'region_id' => $region->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $district = District::create(['organization_id' => $this->org->id, 'province_id' => $province->id, 'name' => 'Miraflores', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $this->store1 = Store::create(['organization_id' => $this->org->id, 'district_id' => $district->id, 'code' => 'ST-001', 'name' => 'Store A', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $this->store2 = Store::create(['organization_id' => $this->org->id, 'district_id' => $district->id, 'code' => 'ST-002', 'name' => 'Store B', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $bank = Bank::create(['organization_id' => $this->org->id, 'code' => 'BCP', 'name' => 'BCP', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $this->agent1 = BankAgent::create(['organization_id' => $this->org->id, 'store_id' => $this->store1->id, 'bank_id' => $bank->id, 'code' => 'AG-001', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $this->agent2 = BankAgent::create(['organization_id' => $this->org->id, 'store_id' => $this->store2->id, 'bank_id' => $bank->id, 'code' => 'AG-002', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $operator = User::factory()->create(['organization_id' => $this->org->id, 'role' => Role::OPERADOR, 'status' => UserStatus::ACTIVE, 'password_changed_at' => now()]);
        UserBankAgentAssignment::create(['user_id' => $operator->id, 'bank_agent_id' => $this->agent1->id, 'assigned_by' => $this->admin->id, 'assigned_at' => now(), 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        UserBankAgentAssignment::create(['user_id' => $operator->id, 'bank_agent_id' => $this->agent2->id, 'assigned_by' => $this->admin->id, 'assigned_at' => now(), 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $this->type = OperationType::create(['organization_id' => $this->org->id, 'bank_id' => null, 'name' => 'Depósito', 'cash_direction' => 'ENTRADA', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        Operation::create([
            'organization_id' => $this->org->id, 'store_id' => $this->store1->id, 'bank_agent_id' => $this->agent1->id,
            'operation_type_id' => $this->type->id, 'user_id' => $operator->id,
            'amount' => 100.00, 'currency' => 'PEN',
            'effective_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'recorded_at' => now(), 'status' => 'ACTIVE',
            'idempotency_key' => Str::random(64), 'created_at' => now(), 'updated_at' => now(),
        ]);

        Operation::create([
            'organization_id' => $this->org->id, 'store_id' => $this->store2->id, 'bank_agent_id' => $this->agent2->id,
            'operation_type_id' => $this->type->id, 'user_id' => $operator->id,
            'amount' => 200.00, 'currency' => 'PEN',
            'effective_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'recorded_at' => now(), 'status' => 'ACTIVE',
            'idempotency_key' => Str::random(64), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_filter_by_store_updates_metrics(): void
    {
        $this->actingAsJwt($this->admin);

        $response = $this->get(route('admin.dashboard', [
            'period' => 'month',
            'store_id' => $this->store1->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('S/ 100.00');
        $response->assertDontSee('S/ 200.00');
    }

    public function test_filter_by_bank_agent_updates_metrics(): void
    {
        $this->actingAsJwt($this->admin);

        $response = $this->get(route('admin.dashboard', [
            'period' => 'month',
            'bank_agent_id' => $this->agent2->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('S/ 200.00');
        $response->assertDontSee('S/ 100.00');
    }
}
