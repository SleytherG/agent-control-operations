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

class OperatorComparisonSingleTest extends TestCase
{
    use RefreshDatabase;

    public function test_single_operator_comparison_works(): void
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
            'username_normalized' => 'solo_operador',
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

        $response = $this->get(route('admin.dashboard.operators', ['period' => 'month']));

        $response->assertStatus(200);
        $response->assertSee('solo_operador');
        $response->assertSee('S/ 100.00');
        $response->assertDontSee('Error');
    }

    public function test_selecting_specific_operator_works(): void
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create([
            'organization_id' => $org->id,
            'role' => Role::ADMINISTRADOR_PROPIETARIO,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);

        $op1 = User::factory()->create([
            'organization_id' => $org->id, 'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE, 'password_changed_at' => now(),
            'username_normalized' => 'select_me',
        ]);
        $op2 = User::factory()->create([
            'organization_id' => $org->id, 'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE, 'password_changed_at' => now(),
            'username_normalized' => 'not_selected',
        ]);

        $region = Region::create(['organization_id' => $org->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $province = Province::create(['organization_id' => $org->id, 'region_id' => $region->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $district = District::create(['organization_id' => $org->id, 'province_id' => $province->id, 'name' => 'Miraflores', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $store = Store::create(['organization_id' => $org->id, 'district_id' => $district->id, 'code' => 'ST-001', 'name' => 'Test Store', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $bank = Bank::create(['organization_id' => $org->id, 'code' => 'BCP', 'name' => 'BCP', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $agent = BankAgent::create(['organization_id' => $org->id, 'store_id' => $store->id, 'bank_id' => $bank->id, 'code' => 'AG-001', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        UserBankAgentAssignment::create(['user_id' => $op1->id, 'bank_agent_id' => $agent->id, 'assigned_by' => $admin->id, 'assigned_at' => now(), 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        UserBankAgentAssignment::create(['user_id' => $op2->id, 'bank_agent_id' => $agent->id, 'assigned_by' => $admin->id, 'assigned_at' => now(), 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $type = OperationType::create(['organization_id' => $org->id, 'bank_id' => null, 'name' => 'Depósito', 'cash_direction' => 'ENTRADA', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        Operation::create([
            'organization_id' => $org->id, 'store_id' => $store->id, 'bank_agent_id' => $agent->id,
            'operation_type_id' => $type->id, 'user_id' => $op1->id,
            'amount' => 55.00, 'currency' => 'PEN',
            'effective_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'recorded_at' => now(), 'status' => 'ACTIVE',
            'idempotency_key' => Str::random(64), 'created_at' => now(), 'updated_at' => now(),
        ]);

        Operation::create([
            'organization_id' => $org->id, 'store_id' => $store->id, 'bank_agent_id' => $agent->id,
            'operation_type_id' => $type->id, 'user_id' => $op2->id,
            'amount' => 999.99, 'currency' => 'PEN',
            'effective_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'recorded_at' => now(), 'status' => 'ACTIVE',
            'idempotency_key' => Str::random(64), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAsJwt($admin);

        $response = $this->get(route('admin.dashboard.operators', [
            'period' => 'month',
            'operator_ids' => [$op1->id],
        ]));

        $response->assertStatus(200);
        $response->assertSee('select_me');
        $response->assertSee('S/ 55.00');
        $response->assertDontSee('S/ 999.99');

        $content = $response->getContent();
        $selectMeTablePos = strpos($content, '<td>select_me</td>');
        $this->assertNotFalse($selectMeTablePos, 'select_me should appear in table');
    }
}
