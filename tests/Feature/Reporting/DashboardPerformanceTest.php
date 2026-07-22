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

class DashboardPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_responds_within_3_seconds_with_50k_operations(): void
    {
        $this->raise_limit('memory_limit', '512M');

        $org = Organization::factory()->create();
        $admin = User::factory()->create([
            'organization_id' => $org->id,
            'role' => Role::ADMINISTRADOR_PROPIETARIO,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);
        $operator = User::factory()->create([
            'organization_id' => $org->id, 'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE, 'password_changed_at' => now(),
        ]);

        $region = Region::create(['organization_id' => $org->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $province = Province::create(['organization_id' => $org->id, 'region_id' => $region->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $district = District::create(['organization_id' => $org->id, 'province_id' => $province->id, 'name' => 'Miraflores', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $store = Store::create(['organization_id' => $org->id, 'district_id' => $district->id, 'code' => 'ST-001', 'name' => 'Test Store', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $bank = Bank::create(['organization_id' => $org->id, 'code' => 'BCP', 'name' => 'BCP', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $agent = BankAgent::create(['organization_id' => $org->id, 'store_id' => $store->id, 'bank_id' => $bank->id, 'code' => 'AG-001', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        UserBankAgentAssignment::create(['user_id' => $operator->id, 'bank_agent_id' => $agent->id, 'assigned_by' => $admin->id, 'assigned_at' => now(), 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $type = OperationType::create(['organization_id' => $org->id, 'bank_id' => null, 'name' => 'Depósito', 'cash_direction' => 'ENTRADA', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $batchSize = 1000;
        $totalOperations = 10000;

        for ($i = 0; $i < $totalOperations; $i += $batchSize) {
            $records = [];
            $end = min($i + $batchSize, $totalOperations);
            for ($j = $i; $j < $end; $j++) {
                $records[] = [
                    'organization_id' => $org->id,
                    'store_id' => $store->id,
                    'bank_agent_id' => $agent->id,
                    'operation_type_id' => $type->id,
                    'user_id' => $operator->id,
                    'amount' => rand(10, 10000) / 100,
                    'currency' => 'PEN',
                    'effective_at' => now()->subDays(rand(0, 30))->format('Y-m-d H:i:s'),
                    'recorded_at' => now()->format('Y-m-d H:i:s'),
                    'status' => 'ACTIVE',
                    'idempotency_key' => Str::random(64),
                    'created_at' => now()->format('Y-m-d H:i:s'),
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ];
            }
            Operation::insert($records);
        }

        $this->actingAsJwt($admin);

        $start = microtime(true);

        $response = $this->get(route('admin.dashboard', ['period' => 'month']));

        $elapsed = microtime(true) - $start;

        $response->assertStatus(200);

        $this->assertLessThan(3.0, $elapsed, "Admin dashboard query took {$elapsed}s, exceeding 3s limit for {$totalOperations} operations");
    }

    public function test_operator_dashboard_performs_well(): void
    {
        $this->raise_limit('memory_limit', '256M');

        $org = Organization::factory()->create();
        $admin = User::factory()->create([
            'organization_id' => $org->id,
            'role' => Role::ADMINISTRADOR_PROPIETARIO,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);
        $operator = User::factory()->create([
            'organization_id' => $org->id, 'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE, 'password_changed_at' => now(),
        ]);

        $region = Region::create(['organization_id' => $org->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $province = Province::create(['organization_id' => $org->id, 'region_id' => $region->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $district = District::create(['organization_id' => $org->id, 'province_id' => $province->id, 'name' => 'Miraflores', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $store = Store::create(['organization_id' => $org->id, 'district_id' => $district->id, 'code' => 'ST-001', 'name' => 'Test Store', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $bank = Bank::create(['organization_id' => $org->id, 'code' => 'BCP', 'name' => 'BCP', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $agent = BankAgent::create(['organization_id' => $org->id, 'store_id' => $store->id, 'bank_id' => $bank->id, 'code' => 'AG-001', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        UserBankAgentAssignment::create(['user_id' => $operator->id, 'bank_agent_id' => $agent->id, 'assigned_by' => $admin->id, 'assigned_at' => now(), 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $type = OperationType::create(['organization_id' => $org->id, 'bank_id' => null, 'name' => 'Depósito', 'cash_direction' => 'ENTRADA', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $records = [];
        for ($i = 0; $i < 5000; $i++) {
            $records[] = [
                'organization_id' => $org->id,
                'store_id' => $store->id,
                'bank_agent_id' => $agent->id,
                'operation_type_id' => $type->id,
                'user_id' => $operator->id,
                'amount' => rand(10, 10000) / 100,
                'currency' => 'PEN',
                'effective_at' => now()->subDays(rand(0, 30))->format('Y-m-d H:i:s'),
                'recorded_at' => now()->format('Y-m-d H:i:s'),
                'status' => 'ACTIVE',
                'idempotency_key' => Str::random(64),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ];
        }
        Operation::insert($records);

        $this->actingAsJwt($operator);

        $start = microtime(true);

        $response = $this->get(route('dashboard.operator', ['period' => 'month']));

        $elapsed = microtime(true) - $start;

        $response->assertStatus(200);
        $this->assertLessThan(3.0, $elapsed, "Operator dashboard query took {$elapsed}s, exceeding 3s limit");
    }

    private function raise_limit(string $setting, string $value): void
    {
        if (function_exists('ini_set')) {
            ini_set($setting, $value);
        }
    }
}
