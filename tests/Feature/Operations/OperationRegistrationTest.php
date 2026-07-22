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
use Illuminate\Support\Str;
use Tests\TestCase;

class OperationRegistrationTest extends TestCase
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

    public function test_operator_can_register_valid_operation(): void
    {
        $this->actingAsJwt($this->operator);

        $idempotencyKey = Str::random(64);

        $response = $this->post(route('operations.store'), [
            'bank_agent_id' => $this->agent->id,
            'operation_type_id' => $this->type->id,
            'amount' => 150.50,
            'currency' => 'PEN',
            'effective_at' => now()->subHours(2)->format('Y-m-d H:i:s'),
            'idempotency_key' => $idempotencyKey,
            'reference' => 'REF-001',
            'observation' => 'Test observation',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('operations', [
            'organization_id' => $this->org->id,
            'bank_agent_id' => $this->agent->id,
            'operation_type_id' => $this->type->id,
            'user_id' => $this->operator->id,
            'amount' => 150.50,
            'currency' => 'PEN',
            'status' => 'ACTIVE',
        ]);

        $operation = Operation::first();
        $this->assertNotNull($operation);
        $this->assertEquals($this->operator->id, $operation->user_id);
        $this->assertEquals('ACTIVE', $operation->status);
    }
}
