<?php

namespace Tests\Feature\Operations;

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
use Tests\TestCase;

class OperationDecimalPrecisionTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $operator;
    private BankAgent $agent;
    private OperationType $type;

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

        $this->type = OperationType::create([
            'organization_id' => $this->org->id, 'bank_id' => null,
            'name' => 'Depósito', 'cash_direction' => 'ENTRADA', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_decimal_precision_with_high_volume(): void
    {
        $preciseAmount = '999999.99';
        $count = 1000;

        for ($i = 0; $i < $count; $i++) {
            Operation::create([
                'organization_id' => $this->org->id,
                'store_id' => $this->agent->store_id,
                'bank_agent_id' => $this->agent->id,
                'operation_type_id' => $this->type->id,
                'user_id' => $this->operator->id,
                'amount' => $preciseAmount,
                'currency' => 'PEN',
                'effective_at' => now(),
                'recorded_at' => now(),
                'status' => 'ACTIVE',
                'idempotency_key' => hash('sha256', 'precision-' . $i),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->assertEquals($count, Operation::count());

        $sum = Operation::where('status', 'ACTIVE')->sum('amount');
        $expectedSum = (string) ($count * (float) $preciseAmount);

        $this->assertEqualsWithDelta((float) $expectedSum, (float) $sum, 0.01);

        $storedAmount = (string) Operation::first()->amount;
        $this->assertEquals($preciseAmount, $storedAmount);
    }

    public function test_amount_is_stored_as_decimal_not_float(): void
    {
        $operation = Operation::create([
            'organization_id' => $this->org->id,
            'store_id' => $this->agent->store_id,
            'bank_agent_id' => $this->agent->id,
            'operation_type_id' => $this->type->id,
            'user_id' => $this->operator->id,
            'amount' => 123.45,
            'currency' => 'PEN',
            'effective_at' => now(),
            'recorded_at' => now(),
            'status' => 'ACTIVE',
            'idempotency_key' => hash('sha256', 'decimal-test'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $fresh = Operation::find($operation->id);
        $this->assertEquals('123.45', (string) $fresh->amount);
    }
}
