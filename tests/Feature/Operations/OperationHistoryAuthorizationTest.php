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
use App\Modules\Operations\Models\OperationType;
use App\Modules\Operations\Models\Operation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationHistoryAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $operator;
    private User $admin;
    private BankAgent $agent;
    private OperationType $type;
    private Operation $ownOp;
    private Operation $otherOp;

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
        $this->admin = User::factory()->create([
            'organization_id' => $this->org->id,
            'role' => Role::ADMINISTRADOR_PROPIETARIO,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);

        $region = Region::create(['organization_id' => $this->org->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $province = Province::create(['organization_id' => $this->org->id, 'region_id' => $region->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $district = District::create(['organization_id' => $this->org->id, 'province_id' => $province->id, 'name' => 'Miraflores', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $store = Store::create(['organization_id' => $this->org->id, 'district_id' => $district->id, 'code' => 'ST-001', 'name' => 'Test Store', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $bank = Bank::create(['organization_id' => $this->org->id, 'code' => 'BCP', 'name' => 'BCP', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $this->agent = BankAgent::create(['organization_id' => $this->org->id, 'store_id' => $store->id, 'bank_id' => $bank->id, 'code' => 'AG-001', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $this->type = OperationType::create([
            'organization_id' => $this->org->id, 'bank_id' => null,
            'name' => 'Depósito', 'cash_direction' => 'ENTRADA', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $otherUser = User::factory()->create([
            'organization_id' => $this->org->id, 'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE, 'password_changed_at' => now(),
        ]);

        $this->ownOp = Operation::create(['organization_id' => $this->org->id, 'store_id' => $store->id, 'bank_agent_id' => $this->agent->id, 'operation_type_id' => $this->type->id, 'user_id' => $this->operator->id, 'amount' => 100.00, 'currency' => 'PEN', 'effective_at' => now(), 'recorded_at' => now(), 'status' => 'ACTIVE', 'idempotency_key' => hash('sha256', 'own'), 'created_at' => now(), 'updated_at' => now()]);
        $this->otherOp = Operation::create(['organization_id' => $this->org->id, 'store_id' => $store->id, 'bank_agent_id' => $this->agent->id, 'operation_type_id' => $this->type->id, 'user_id' => $otherUser->id, 'amount' => 200.00, 'currency' => 'PEN', 'effective_at' => now(), 'recorded_at' => now(), 'status' => 'ACTIVE', 'idempotency_key' => hash('sha256', 'other'), 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_operator_cannot_see_other_users_operation_detail(): void
    {
        $this->actingAsJwt($this->operator);

        $response = $this->get(route('operations.show', $this->otherOp));

        $response->assertForbidden();
    }

    public function test_operator_can_see_own_operation_detail(): void
    {
        $this->actingAsJwt($this->operator);

        $response = $this->get(route('operations.show', $this->ownOp));

        $response->assertStatus(200);
        $response->assertSee('100.00');
    }

    public function test_admin_can_see_any_operation(): void
    {
        $this->actingAsJwt($this->admin);

        $response = $this->get(route('operations.show', $this->otherOp));

        $response->assertStatus(200);
    }
}
