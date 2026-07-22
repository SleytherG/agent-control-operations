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

class OperationHistoryAdminTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $admin;
    private User $operator1;
    private User $operator2;
    private BankAgent $agent1;
    private BankAgent $agent2;
    private OperationType $type1;
    private OperationType $type2;

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
        $this->operator1 = User::factory()->create([
            'organization_id' => $this->org->id, 'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE, 'password_changed_at' => now(),
        ]);
        $this->operator2 = User::factory()->create([
            'organization_id' => $this->org->id, 'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE, 'password_changed_at' => now(),
        ]);

        $region = Region::create(['organization_id' => $this->org->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $province = Province::create(['organization_id' => $this->org->id, 'region_id' => $region->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $district = District::create(['organization_id' => $this->org->id, 'province_id' => $province->id, 'name' => 'Miraflores', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $store = Store::create(['organization_id' => $this->org->id, 'district_id' => $district->id, 'code' => 'ST-001', 'name' => 'Test Store', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $bank = Bank::create(['organization_id' => $this->org->id, 'code' => 'BCP', 'name' => 'BCP', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $this->agent1 = BankAgent::create(['organization_id' => $this->org->id, 'store_id' => $store->id, 'bank_id' => $bank->id, 'code' => 'AG-001', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $this->agent2 = BankAgent::create(['organization_id' => $this->org->id, 'store_id' => $store->id, 'bank_id' => $bank->id, 'code' => 'AG-002', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $this->type1 = OperationType::create(['organization_id' => $this->org->id, 'bank_id' => null, 'name' => 'Depósito', 'cash_direction' => 'ENTRADA', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $this->type2 = OperationType::create(['organization_id' => $this->org->id, 'bank_id' => null, 'name' => 'Retiro', 'cash_direction' => 'SALIDA', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        Operation::create(['organization_id' => $this->org->id, 'store_id' => $store->id, 'bank_agent_id' => $this->agent1->id, 'operation_type_id' => $this->type1->id, 'user_id' => $this->operator1->id, 'amount' => 100.00, 'currency' => 'PEN', 'effective_at' => now(), 'recorded_at' => now(), 'status' => 'ACTIVE', 'idempotency_key' => hash('sha256', 'a1'), 'created_at' => now(), 'updated_at' => now()]);
        Operation::create(['organization_id' => $this->org->id, 'store_id' => $store->id, 'bank_agent_id' => $this->agent2->id, 'operation_type_id' => $this->type2->id, 'user_id' => $this->operator2->id, 'amount' => 200.00, 'currency' => 'PEN', 'effective_at' => now(), 'recorded_at' => now(), 'status' => 'ACTIVE', 'idempotency_key' => hash('sha256', 'a2'), 'created_at' => now(), 'updated_at' => now()]);
        Operation::create(['organization_id' => $this->org->id, 'store_id' => $store->id, 'bank_agent_id' => $this->agent1->id, 'operation_type_id' => $this->type1->id, 'user_id' => $this->operator1->id, 'amount' => 300.00, 'currency' => 'PEN', 'effective_at' => now(), 'recorded_at' => now(), 'status' => 'ANNULLED', 'annulled_by' => $this->admin->id, 'annulled_at' => now(), 'annulment_reason' => 'Error', 'idempotency_key' => hash('sha256', 'a3'), 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_admin_sees_all_operations(): void
    {
        $this->actingAsJwt($this->admin);

        $response = $this->get(route('operations.index'));

        $response->assertStatus(200);
        $response->assertSee('100.00');
        $response->assertSee('200.00');
        $response->assertSee('300.00');
    }

    public function test_admin_can_filter_by_agent(): void
    {
        $this->actingAsJwt($this->admin);

        $response = $this->get(route('operations.index', ['bank_agent_id' => $this->agent1->id]));

        $response->assertStatus(200);
        $response->assertSee('100.00');
        $response->assertSee('300.00');
        $response->assertDontSee('200.00');
    }

    public function test_admin_can_filter_by_status(): void
    {
        $this->actingAsJwt($this->admin);

        $response = $this->get(route('operations.index', ['status' => 'ANNULLED']));

        $response->assertStatus(200);
        $response->assertSee('300.00');
        $response->assertSee('Anulada');
        $response->assertDontSee('100.00');
    }

    public function test_admin_can_filter_by_date_range(): void
    {
        $this->actingAsJwt($this->admin);

        $ops = Operation::where('status', 'ACTIVE')->orderBy('id')->get();

        $response = $this->get(route('operations.index', [
            'date_from' => now()->subDay()->toDateString(),
            'date_to' => now()->addDay()->toDateString(),
        ]));

        $response->assertStatus(200);
    }
}
