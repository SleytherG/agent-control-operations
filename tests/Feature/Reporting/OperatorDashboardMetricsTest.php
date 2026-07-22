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

class OperatorDashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $operator;
    private BankAgent $agent;
    private OperationType $entryType;
    private OperationType $exitType;

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

        $region = Region::create(['organization_id' => $this->org->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $province = Province::create(['organization_id' => $this->org->id, 'region_id' => $region->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $district = District::create(['organization_id' => $this->org->id, 'province_id' => $province->id, 'name' => 'Miraflores', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $store = Store::create(['organization_id' => $this->org->id, 'district_id' => $district->id, 'code' => 'ST-001', 'name' => 'Test Store', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $bank = Bank::create(['organization_id' => $this->org->id, 'code' => 'BCP', 'name' => 'BCP', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $this->agent = BankAgent::create(['organization_id' => $this->org->id, 'store_id' => $store->id, 'bank_id' => $bank->id, 'code' => 'AG-001', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $admin = User::factory()->create(['organization_id' => $this->org->id, 'role' => Role::ADMINISTRADOR_PROPIETARIO, 'status' => UserStatus::ACTIVE, 'password_changed_at' => now()]);

        UserBankAgentAssignment::create([
            'user_id' => $this->operator->id, 'bank_agent_id' => $this->agent->id,
            'assigned_by' => $admin->id, 'assigned_at' => now(),
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->entryType = OperationType::create([
            'organization_id' => $this->org->id, 'bank_id' => null,
            'name' => 'Depósito', 'cash_direction' => 'ENTRADA', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->exitType = OperationType::create([
            'organization_id' => $this->org->id, 'bank_id' => null,
            'name' => 'Retiro', 'cash_direction' => 'SALIDA', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_operator_dashboard_shows_correct_metrics(): void
    {
        Operation::create([
            'organization_id' => $this->org->id,
            'store_id' => $this->agent->store_id,
            'bank_agent_id' => $this->agent->id,
            'operation_type_id' => $this->entryType->id,
            'user_id' => $this->operator->id,
            'amount' => 500.00,
            'currency' => 'PEN',
            'effective_at' => now()->subHours(2)->format('Y-m-d H:i:s'),
            'recorded_at' => now(),
            'status' => 'ACTIVE',
            'idempotency_key' => Str::random(64),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Operation::create([
            'organization_id' => $this->org->id,
            'store_id' => $this->agent->store_id,
            'bank_agent_id' => $this->agent->id,
            'operation_type_id' => $this->exitType->id,
            'user_id' => $this->operator->id,
            'amount' => 200.00,
            'currency' => 'PEN',
            'effective_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'recorded_at' => now(),
            'status' => 'ACTIVE',
            'idempotency_key' => Str::random(64),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->operator);

        $response = $this->get(route('dashboard.operator', ['period' => 'month']));

        $response->assertStatus(200);
        $response->assertSee('Monto bruto operado');
        $response->assertSee('S/ 700.00');
        $response->assertSee('S/ 500.00'); // cash_in
        $response->assertSee('S/ 200.00'); // cash_out
        $response->assertSee('S/ 300.00'); // net_movement = 500 - 200
        $response->assertSee('Entradas de efectivo');
        $response->assertSee('Salidas de efectivo');
        $response->assertSee('Movimiento neto');
    }

    public function test_operator_only_sees_own_operations(): void
    {
        $otherOperator = User::factory()->create([
            'organization_id' => $this->org->id,
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);

        Operation::create([
            'organization_id' => $this->org->id,
            'store_id' => $this->agent->store_id,
            'bank_agent_id' => $this->agent->id,
            'operation_type_id' => $this->entryType->id,
            'user_id' => $this->operator->id,
            'amount' => 100.00,
            'currency' => 'PEN',
            'effective_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'recorded_at' => now(),
            'status' => 'ACTIVE',
            'idempotency_key' => Str::random(64),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Operation::create([
            'organization_id' => $this->org->id,
            'store_id' => $this->agent->store_id,
            'bank_agent_id' => $this->agent->id,
            'operation_type_id' => $this->entryType->id,
            'user_id' => $otherOperator->id,
            'amount' => 999.99,
            'currency' => 'PEN',
            'effective_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'recorded_at' => now(),
            'status' => 'ACTIVE',
            'idempotency_key' => Str::random(64),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->operator);

        $response = $this->get(route('dashboard.operator', ['period' => 'month']));

        $response->assertStatus(200);
        $response->assertSee('S/ 100.00');
        $response->assertDontSee('S/ 999.99');
    }

    public function test_operator_with_no_operations_sees_zero_metrics(): void
    {
        $this->actingAsJwt($this->operator);

        $response = $this->get(route('dashboard.operator'));

        $response->assertStatus(200);
        $response->assertSee('Sin operaciones por ahora');
        $response->assertSee('0');
    }
}
