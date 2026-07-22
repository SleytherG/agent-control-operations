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

class GenerateClosingTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $admin;
    private BankAgent $agent;
    private OperationType $typeEntrada;
    private OperationType $typeSalida;
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

        $region = Region::create(['organization_id' => $this->org->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $province = Province::create(['organization_id' => $this->org->id, 'region_id' => $region->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $district = District::create(['organization_id' => $this->org->id, 'province_id' => $province->id, 'name' => 'Miraflores', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $store = Store::create(['organization_id' => $this->org->id, 'district_id' => $district->id, 'code' => 'ST-001', 'name' => 'Test Store', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $bank = Bank::create(['organization_id' => $this->org->id, 'code' => 'BCP', 'name' => 'BCP', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $this->agent = BankAgent::create(['organization_id' => $this->org->id, 'store_id' => $store->id, 'bank_id' => $bank->id, 'code' => 'AG-001', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $this->typeEntrada = OperationType::create(['organization_id' => $this->org->id, 'bank_id' => null, 'name' => 'Depósito', 'cash_direction' => 'ENTRADA', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $this->typeSalida = OperationType::create(['organization_id' => $this->org->id, 'bank_id' => null, 'name' => 'Retiro', 'cash_direction' => 'SALIDA', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $this->businessDate = now()->format('Y-m-d');
    }

    public function test_generate_closure_with_correct_metrics(): void
    {
        Operation::create([
            'organization_id' => $this->org->id, 'store_id' => $this->agent->store_id,
            'bank_agent_id' => $this->agent->id, 'operation_type_id' => $this->typeEntrada->id,
            'user_id' => $this->admin->id, 'amount' => 100.50, 'currency' => 'PEN',
            'effective_at' => $this->businessDate . ' 10:00:00', 'recorded_at' => now(),
            'status' => Operation::STATUS_ACTIVE,
            'idempotency_key' => \Illuminate\Support\Str::random(64),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        Operation::create([
            'organization_id' => $this->org->id, 'store_id' => $this->agent->store_id,
            'bank_agent_id' => $this->agent->id, 'operation_type_id' => $this->typeSalida->id,
            'user_id' => $this->admin->id, 'amount' => 50.25, 'currency' => 'PEN',
            'effective_at' => $this->businessDate . ' 11:00:00', 'recorded_at' => now(),
            'status' => Operation::STATUS_ACTIVE,
            'idempotency_key' => \Illuminate\Support\Str::random(64),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->admin);

        $response = $this->post(route('daily-closures.store'), [
            'bank_agent_id' => $this->agent->id,
            'business_date' => $this->businessDate,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $closure = DailyClosure::first();
        $this->assertNotNull($closure);
        $this->assertEquals(DailyClosure::STATUS_ACTIVO, $closure->status);
        $this->assertEquals(2, $closure->operation_count);
        $this->assertEquals(150.75, (float) (string) $closure->gross_amount);
        $this->assertEquals(100.50, (float) (string) $closure->cash_in);
        $this->assertEquals(50.25, (float) (string) $closure->cash_out);
        $this->assertEquals(50.25, (float) (string) $closure->net_movement);
    }

    public function test_closure_excludes_annulled_operations(): void
    {
        Operation::create([
            'organization_id' => $this->org->id, 'store_id' => $this->agent->store_id,
            'bank_agent_id' => $this->agent->id, 'operation_type_id' => $this->typeEntrada->id,
            'user_id' => $this->admin->id, 'amount' => 200.00, 'currency' => 'PEN',
            'effective_at' => $this->businessDate . ' 10:00:00', 'recorded_at' => now(),
            'status' => Operation::STATUS_ACTIVE,
            'idempotency_key' => \Illuminate\Support\Str::random(64),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        Operation::create([
            'organization_id' => $this->org->id, 'store_id' => $this->agent->store_id,
            'bank_agent_id' => $this->agent->id, 'operation_type_id' => $this->typeEntrada->id,
            'user_id' => $this->admin->id, 'amount' => 99.99, 'currency' => 'PEN',
            'effective_at' => $this->businessDate . ' 11:00:00', 'recorded_at' => now(),
            'status' => Operation::STATUS_ANNULLED, 'annulled_by' => $this->admin->id,
            'annulled_at' => now(), 'annulment_reason' => 'Test',
            'idempotency_key' => \Illuminate\Support\Str::random(64),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->admin);

        $this->post(route('daily-closures.store'), [
            'bank_agent_id' => $this->agent->id,
            'business_date' => $this->businessDate,
        ]);

        $closure = DailyClosure::first();
        $this->assertEquals(1, $closure->operation_count);
        $this->assertEquals(200.00, (float) (string) $closure->gross_amount);
    }

    public function test_regenerate_updates_closure_metrics(): void
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

        $this->actingAsJwt($this->admin);

        $this->post(route('daily-closures.store'), [
            'bank_agent_id' => $this->agent->id,
            'business_date' => $this->businessDate,
        ]);

        $closure = DailyClosure::first();
        $this->assertEquals(1, $closure->operation_count);
        $this->assertEquals(100.00, (float) (string) $closure->gross_amount);

        $this->post(route('daily-closures.store'), [
            'bank_agent_id' => $this->agent->id,
            'business_date' => $this->businessDate,
            'regenerate' => true,
        ]);

        $closure->refresh();
        $this->assertGreaterThanOrEqual(1, $closure->operation_count);
        $this->assertEquals(100.00, (float) (string) $closure->gross_amount);
    }

    public function test_duplicate_active_closure_redirects(): void
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

        $this->actingAsJwt($this->admin);

        $this->post(route('daily-closures.store'), [
            'bank_agent_id' => $this->agent->id,
            'business_date' => $this->businessDate,
        ]);

        $response = $this->post(route('daily-closures.store'), [
            'bank_agent_id' => $this->agent->id,
            'business_date' => $this->businessDate,
        ]);

        $response->assertRedirect();
        $this->assertEquals(1, DailyClosure::count());
    }
}
