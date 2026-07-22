<?php

namespace Tests\Feature\DailyClosing;

use App\Modules\BankingNetwork\Models\Bank;
use App\Modules\BankingNetwork\Models\BankAgent;
use App\Modules\BankingNetwork\Models\UserBankAgentAssignment;
use App\Modules\DailyClosing\Models\DailyClosure;
use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Operations\Models\Operation;
use App\Modules\Operations\Models\OperationType;
use App\Modules\Organization\Models\District;
use App\Modules\Organization\Models\Organization;
use App\Modules\Organization\Models\Province;
use App\Modules\Organization\Models\Region;
use App\Modules\Organization\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClosingQuickstartTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $admin;
    private User $operator;
    private BankAgent $agent;
    private OperationType $typeEntrada;
    private OperationType $typeSalida;
    private OperationType $typePending;
    private string $businessDate;

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

        UserBankAgentAssignment::create([
            'user_id' => $this->operator->id, 'bank_agent_id' => $this->agent->id,
            'assigned_by' => $this->admin->id, 'assigned_at' => now(),
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->typeEntrada = OperationType::create(['organization_id' => $this->org->id, 'bank_id' => null, 'name' => 'Depósito', 'cash_direction' => 'ENTRADA', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $this->typeSalida = OperationType::create(['organization_id' => $this->org->id, 'bank_id' => null, 'name' => 'Retiro', 'cash_direction' => 'SALIDA', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $this->typePending = OperationType::create(['organization_id' => $this->org->id, 'bank_id' => null, 'name' => 'Transferencia Pendiente', 'cash_direction' => 'POR_CONFIRMAR', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $this->businessDate = now()->format('Y-m-d');
    }

    public function test_generate_closure_flow(): void
    {
        Operation::create([
            'organization_id' => $this->org->id, 'store_id' => $this->agent->store_id,
            'bank_agent_id' => $this->agent->id, 'operation_type_id' => $this->typeEntrada->id,
            'user_id' => $this->admin->id, 'amount' => 150.00, 'currency' => 'PEN',
            'effective_at' => $this->businessDate . ' 10:00:00', 'recorded_at' => now(),
            'status' => Operation::STATUS_ACTIVE,
            'idempotency_key' => \Illuminate\Support\Str::random(64),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->admin);
        $this->get(route('daily-closures.create'))->assertOk();

        $response = $this->post(route('daily-closures.store'), [
            'bank_agent_id' => $this->agent->id,
            'business_date' => $this->businessDate,
        ]);

        $response->assertSessionHas('status');
        $this->assertEquals(1, DailyClosure::count());
    }

    public function test_operator_generate_for_unassigned_agent_is_forbidden(): void
    {
        $otherAgent = BankAgent::create(['organization_id' => $this->org->id, 'store_id' => $this->agent->store_id, 'bank_id' => $this->agent->bank_id, 'code' => 'AG-999', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAsJwt($this->operator);

        $response = $this->post(route('daily-closures.store'), [
            'bank_agent_id' => $otherAgent->id,
            'business_date' => $this->businessDate,
        ]);

        $response->assertForbidden();
    }

    public function test_full_confirm_cycle(): void
    {
        Operation::create([
            'organization_id' => $this->org->id, 'store_id' => $this->agent->store_id,
            'bank_agent_id' => $this->agent->id, 'operation_type_id' => $this->typeEntrada->id,
            'user_id' => $this->operator->id, 'amount' => 100.00, 'currency' => 'PEN',
            'effective_at' => $this->businessDate . ' 10:00:00', 'recorded_at' => now(),
            'status' => Operation::STATUS_ACTIVE,
            'idempotency_key' => \Illuminate\Support\Str::random(64),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->admin);
        $this->post(route('daily-closures.store'), ['bank_agent_id' => $this->agent->id, 'business_date' => $this->businessDate]);
        $closure = DailyClosure::first();
        $this->assertEquals(DailyClosure::STATUS_ACTIVO, $closure->status);

        $this->post(route('daily-closures.confirm', $closure));
        $this->assertEquals(DailyClosure::STATUS_CONFIRMADO, $closure->fresh()->status);

        $operation = Operation::first();
        $this->actingAsJwt($this->operator);
        $this->post(route('operations.annul', $operation), ['reason' => 'Intento post confirmación']);

        $operation->refresh();
        $this->assertEquals(Operation::STATUS_ACTIVE, $operation->status);
    }

    public function test_reopen_and_reconfirm_cycle(): void
    {
        Operation::create([
            'organization_id' => $this->org->id, 'store_id' => $this->agent->store_id,
            'bank_agent_id' => $this->agent->id, 'operation_type_id' => $this->typeEntrada->id,
            'user_id' => $this->operator->id, 'amount' => 100.00, 'currency' => 'PEN',
            'effective_at' => $this->businessDate . ' 10:00:00', 'recorded_at' => now(),
            'status' => Operation::STATUS_ACTIVE,
            'idempotency_key' => \Illuminate\Support\Str::random(64),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->admin);
        $this->post(route('daily-closures.store'), ['bank_agent_id' => $this->agent->id, 'business_date' => $this->businessDate]);
        $closure = DailyClosure::first();

        $this->post(route('daily-closures.confirm', $closure));
        $this->post(route('daily-closures.reopen', $closure), ['reason' => 'Corrección necesaria']);
        $this->assertEquals(DailyClosure::STATUS_REABIERTO, $closure->fresh()->status);

        $this->post(route('daily-closures.confirm', $closure));
        $this->assertEquals(DailyClosure::STATUS_CONFIRMADO, $closure->fresh()->status);
        $this->assertNotNull($closure->fresh()->confirmed_at);

        $this->actingAsJwt($this->operator);
        $response = $this->post(route('daily-closures.reopen', $closure), ['reason' => 'Intento operador']);
        $response->assertForbidden();
    }

    public function test_pending_confirm_warning_in_generated_closure(): void
    {
        Operation::create([
            'organization_id' => $this->org->id, 'store_id' => $this->agent->store_id,
            'bank_agent_id' => $this->agent->id, 'operation_type_id' => $this->typeEntrada->id,
            'user_id' => $this->admin->id, 'amount' => 100.00, 'currency' => 'PEN',
            'effective_at' => $this->businessDate . ' 10:00:00', 'recorded_at' => now(),
            'status' => Operation::STATUS_ACTIVE,
            'idempotency_key' => \Illuminate\Support\Str::random(64),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        Operation::create([
            'organization_id' => $this->org->id, 'store_id' => $this->agent->store_id,
            'bank_agent_id' => $this->agent->id, 'operation_type_id' => $this->typePending->id,
            'user_id' => $this->admin->id, 'amount' => 50.00, 'currency' => 'PEN',
            'effective_at' => $this->businessDate . ' 11:00:00', 'recorded_at' => now(),
            'status' => Operation::STATUS_ACTIVE,
            'idempotency_key' => \Illuminate\Support\Str::random(64),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->admin);
        $this->post(route('daily-closures.store'), ['bank_agent_id' => $this->agent->id, 'business_date' => $this->businessDate]);

        $closure = DailyClosure::first();
        $this->assertTrue($closure->has_pending_confirm);

        $response = $this->get(route('daily-closures.show', $closure));
        $response->assertSee('Pendiente de confirmación');
    }
}
