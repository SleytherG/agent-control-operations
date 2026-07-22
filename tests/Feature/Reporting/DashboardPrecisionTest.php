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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardPrecisionTest extends TestCase
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

        $region = Region::create(['organization_id' => $this->org->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $province = Province::create(['organization_id' => $this->org->id, 'region_id' => $region->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $district = District::create(['organization_id' => $this->org->id, 'province_id' => $province->id, 'name' => 'Miraflores', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $store = Store::create(['organization_id' => $this->org->id, 'district_id' => $district->id, 'code' => 'ST-001', 'name' => 'Test Store', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $bank = Bank::create(['organization_id' => $this->org->id, 'code' => 'BCP', 'name' => 'BCP', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $agent = BankAgent::create(['organization_id' => $this->org->id, 'store_id' => $store->id, 'bank_id' => $bank->id, 'code' => 'AG-001', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $admin = User::factory()->create(['organization_id' => $this->org->id, 'role' => Role::ADMINISTRADOR_PROPIETARIO, 'status' => UserStatus::ACTIVE, 'password_changed_at' => now()]);
        UserBankAgentAssignment::create([
            'user_id' => $this->operator->id, 'bank_agent_id' => $agent->id,
            'assigned_by' => $admin->id, 'assigned_at' => now(),
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_aggregations_match_direct_sql_query(): void
    {
        $entryType = OperationType::create(['organization_id' => $this->org->id, 'bank_id' => null, 'name' => 'Depósito', 'cash_direction' => 'ENTRADA', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $exitType = OperationType::create(['organization_id' => $this->org->id, 'bank_id' => null, 'name' => 'Retiro', 'cash_direction' => 'SALIDA', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $neutralType = OperationType::create(['organization_id' => $this->org->id, 'bank_id' => null, 'name' => 'Consulta', 'cash_direction' => 'NEUTRA', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $store = Store::query()->first();
        $agent = BankAgent::query()->first();

        Operation::create([
            'organization_id' => $this->org->id, 'store_id' => $store->id, 'bank_agent_id' => $agent->id,
            'operation_type_id' => $entryType->id, 'user_id' => $this->operator->id,
            'amount' => 150.00, 'currency' => 'PEN',
            'effective_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'recorded_at' => now(), 'status' => 'ACTIVE',
            'idempotency_key' => Str::random(64), 'created_at' => now(), 'updated_at' => now(),
        ]);

        Operation::create([
            'organization_id' => $this->org->id, 'store_id' => $store->id, 'bank_agent_id' => $agent->id,
            'operation_type_id' => $entryType->id, 'user_id' => $this->operator->id,
            'amount' => 250.00, 'currency' => 'PEN',
            'effective_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'recorded_at' => now(), 'status' => 'ACTIVE',
            'idempotency_key' => Str::random(64), 'created_at' => now(), 'updated_at' => now(),
        ]);

        Operation::create([
            'organization_id' => $this->org->id, 'store_id' => $store->id, 'bank_agent_id' => $agent->id,
            'operation_type_id' => $exitType->id, 'user_id' => $this->operator->id,
            'amount' => 50.00, 'currency' => 'PEN',
            'effective_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'recorded_at' => now(), 'status' => 'ACTIVE',
            'idempotency_key' => Str::random(64), 'created_at' => now(), 'updated_at' => now(),
        ]);

        Operation::create([
            'organization_id' => $this->org->id, 'store_id' => $store->id, 'bank_agent_id' => $agent->id,
            'operation_type_id' => $neutralType->id, 'user_id' => $this->operator->id,
            'amount' => 10.00, 'currency' => 'PEN',
            'effective_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'recorded_at' => now(), 'status' => 'ACTIVE',
            'idempotency_key' => Str::random(64), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $directResult = DB::table('operations as o')
            ->join('operation_types as ot', 'o.operation_type_id', '=', 'ot.id')
            ->where('o.status', 'ACTIVE')
            ->where('o.user_id', $this->operator->id)
            ->whereBetween('o.effective_at', [now()->startOfMonth()->setTimezone('UTC'), now()->endOfMonth()->setTimezone('UTC')])
            ->selectRaw("
                COUNT(*) as operation_count,
                COALESCE(SUM(o.amount), 0) as gross_amount,
                COALESCE(SUM(CASE WHEN ot.cash_direction = 'ENTRADA' THEN o.amount ELSE 0 END), 0) as cash_in,
                COALESCE(SUM(CASE WHEN ot.cash_direction = 'SALIDA' THEN o.amount ELSE 0 END), 0) as cash_out,
                COALESCE(SUM(CASE WHEN ot.cash_direction = 'ENTRADA' THEN o.amount ELSE 0 END), 0)
                  - COALESCE(SUM(CASE WHEN ot.cash_direction = 'SALIDA' THEN o.amount ELSE 0 END), 0) as net_movement
            ")
            ->first();

        $this->assertEquals(4, $directResult->operation_count);
        $this->assertEquals('460.00', $directResult->gross_amount);
        $this->assertEquals('400.00', $directResult->cash_in);
        $this->assertEquals('50.00', $directResult->cash_out);
        $this->assertEquals('350.00', $directResult->net_movement);

        $this->actingAsJwt($this->operator);

        $response = $this->get(route('dashboard.operator', ['period' => 'month']));

        $response->assertStatus(200);
        $response->assertSee('S/ 460.00');
        $response->assertSee('S/ 400.00');
        $response->assertSee('S/ 50.00');
        $response->assertSee('S/ 350.00');
    }

    public function test_decimal_precision_is_maintained(): void
    {
        $store = Store::query()->first();
        $agent = BankAgent::query()->first();

        $type = OperationType::create(['organization_id' => $this->org->id, 'bank_id' => null, 'name' => 'Depósito', 'cash_direction' => 'ENTRADA', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        Operation::create([
            'organization_id' => $this->org->id, 'store_id' => $store->id, 'bank_agent_id' => $agent->id,
            'operation_type_id' => $type->id, 'user_id' => $this->operator->id,
            'amount' => 0.99, 'currency' => 'PEN',
            'effective_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'recorded_at' => now(), 'status' => 'ACTIVE',
            'idempotency_key' => Str::random(64), 'created_at' => now(), 'updated_at' => now(),
        ]);

        Operation::create([
            'organization_id' => $this->org->id, 'store_id' => $store->id, 'bank_agent_id' => $agent->id,
            'operation_type_id' => $type->id, 'user_id' => $this->operator->id,
            'amount' => 0.01, 'currency' => 'PEN',
            'effective_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'recorded_at' => now(), 'status' => 'ACTIVE',
            'idempotency_key' => Str::random(64), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->operator);

        $response = $this->get(route('dashboard.operator', ['period' => 'month']));

        $response->assertStatus(200);
        $response->assertSee('S/ 1.00');
    }
}
