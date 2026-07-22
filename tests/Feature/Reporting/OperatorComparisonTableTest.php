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

class OperatorComparisonTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_comparison_table_shows_ranking_ordered_by_gross_amount(): void
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create([
            'organization_id' => $org->id,
            'role' => Role::ADMINISTRADOR_PROPIETARIO,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);

        $opA = User::factory()->create([
            'organization_id' => $org->id, 'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE, 'password_changed_at' => now(),
            'username_normalized' => 'operator_a',
        ]);
        $opB = User::factory()->create([
            'organization_id' => $org->id, 'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE, 'password_changed_at' => now(),
            'username_normalized' => 'operator_b',
        ]);

        $region = Region::create(['organization_id' => $org->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $province = Province::create(['organization_id' => $org->id, 'region_id' => $region->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $district = District::create(['organization_id' => $org->id, 'province_id' => $province->id, 'name' => 'Miraflores', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $store = Store::create(['organization_id' => $org->id, 'district_id' => $district->id, 'code' => 'ST-001', 'name' => 'Test Store', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $bank = Bank::create(['organization_id' => $org->id, 'code' => 'BCP', 'name' => 'BCP', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $agent = BankAgent::create(['organization_id' => $org->id, 'store_id' => $store->id, 'bank_id' => $bank->id, 'code' => 'AG-001', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        UserBankAgentAssignment::create(['user_id' => $opA->id, 'bank_agent_id' => $agent->id, 'assigned_by' => $admin->id, 'assigned_at' => now(), 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        UserBankAgentAssignment::create(['user_id' => $opB->id, 'bank_agent_id' => $agent->id, 'assigned_by' => $admin->id, 'assigned_at' => now(), 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $type = OperationType::create(['organization_id' => $org->id, 'bank_id' => null, 'name' => 'Depósito', 'cash_direction' => 'ENTRADA', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        Operation::create([
            'organization_id' => $org->id, 'store_id' => $store->id, 'bank_agent_id' => $agent->id,
            'operation_type_id' => $type->id, 'user_id' => $opB->id,
            'amount' => 1000.00, 'currency' => 'PEN',
            'effective_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'recorded_at' => now(), 'status' => 'ACTIVE',
            'idempotency_key' => Str::random(64), 'created_at' => now(), 'updated_at' => now(),
        ]);

        Operation::create([
            'organization_id' => $org->id, 'store_id' => $store->id, 'bank_agent_id' => $agent->id,
            'operation_type_id' => $type->id, 'user_id' => $opA->id,
            'amount' => 250.00, 'currency' => 'PEN',
            'effective_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'recorded_at' => now(), 'status' => 'ACTIVE',
            'idempotency_key' => Str::random(64), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAsJwt($admin);

        $response = $this->get(route('admin.dashboard.operators', ['period' => 'month']));

        $response->assertStatus(200);

        $content = $response->getContent();

        $tbodyStart = strpos($content, '<tbody>');
        $tbodyContent = substr($content, $tbodyStart);

        $posA = strpos($tbodyContent, 'operator_a');
        $posB = strpos($tbodyContent, 'operator_b');

        $this->assertNotFalse($posA);
        $this->assertNotFalse($posB);
        $this->assertLessThan($posA, $posB, 'operator_b with higher amount should appear first in table');
    }

    public function test_comparison_table_has_required_columns(): void
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create([
            'organization_id' => $org->id,
            'role' => Role::ADMINISTRADOR_PROPIETARIO,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);

        $op = User::factory()->create([
            'organization_id' => $org->id, 'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE, 'password_changed_at' => now(),
            'username_normalized' => 'table_op',
        ]);

        $region = Region::create(['organization_id' => $org->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $province = Province::create(['organization_id' => $org->id, 'region_id' => $region->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $district = District::create(['organization_id' => $org->id, 'province_id' => $province->id, 'name' => 'Miraflores', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $store = Store::create(['organization_id' => $org->id, 'district_id' => $district->id, 'code' => 'ST-001', 'name' => 'Test Store', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $bank = Bank::create(['organization_id' => $org->id, 'code' => 'BCP', 'name' => 'BCP', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $agent = BankAgent::create(['organization_id' => $org->id, 'store_id' => $store->id, 'bank_id' => $bank->id, 'code' => 'AG-001', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        UserBankAgentAssignment::create(['user_id' => $op->id, 'bank_agent_id' => $agent->id, 'assigned_by' => $admin->id, 'assigned_at' => now(), 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $type = OperationType::create(['organization_id' => $org->id, 'bank_id' => null, 'name' => 'Depósito', 'cash_direction' => 'ENTRADA', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        Operation::create([
            'organization_id' => $org->id, 'store_id' => $store->id, 'bank_agent_id' => $agent->id,
            'operation_type_id' => $type->id, 'user_id' => $op->id,
            'amount' => 100.00, 'currency' => 'PEN',
            'effective_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'recorded_at' => now(), 'status' => 'ACTIVE',
            'idempotency_key' => Str::random(64), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAsJwt($admin);

        $response = $this->get(route('admin.dashboard.operators'));

        $response->assertStatus(200);
        $response->assertSee('Ranking de operadores');
        $response->assertSee('Monto bruto operado');
    }
}
