<?php

namespace Tests\Feature\DailyClosing;

use App\Modules\BankingNetwork\Models\Bank;
use App\Modules\BankingNetwork\Models\BankAgent;
use App\Modules\BankingNetwork\Models\UserBankAgentAssignment;
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

class ClosingOperatorAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $admin;
    private User $operator;
    private BankAgent $assignedAgent;
    private BankAgent $unassignedAgent;
    private OperationType $type;
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

        $this->assignedAgent = BankAgent::create(['organization_id' => $this->org->id, 'store_id' => $store->id, 'bank_id' => $bank->id, 'code' => 'AG-001', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $this->unassignedAgent = BankAgent::create(['organization_id' => $this->org->id, 'store_id' => $store->id, 'bank_id' => $bank->id, 'code' => 'AG-002', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        UserBankAgentAssignment::create([
            'user_id' => $this->operator->id, 'bank_agent_id' => $this->assignedAgent->id,
            'assigned_by' => $this->admin->id, 'assigned_at' => now(),
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->type = OperationType::create(['organization_id' => $this->org->id, 'bank_id' => null, 'name' => 'Depósito', 'cash_direction' => 'ENTRADA', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $this->businessDate = now()->format('Y-m-d');
    }

    public function test_operator_can_generate_closure_for_assigned_agent(): void
    {
        Operation::create([
            'organization_id' => $this->org->id, 'store_id' => $this->assignedAgent->store_id,
            'bank_agent_id' => $this->assignedAgent->id, 'operation_type_id' => $this->type->id,
            'user_id' => $this->operator->id, 'amount' => 100.00, 'currency' => 'PEN',
            'effective_at' => $this->businessDate . ' 10:00:00', 'recorded_at' => now(),
            'status' => Operation::STATUS_ACTIVE,
            'idempotency_key' => \Illuminate\Support\Str::random(64),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->operator);

        $response = $this->post(route('daily-closures.store'), [
            'bank_agent_id' => $this->assignedAgent->id,
            'business_date' => $this->businessDate,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');
    }

    public function test_operator_cannot_generate_closure_for_unassigned_agent(): void
    {
        $this->actingAsJwt($this->operator);

        $response = $this->post(route('daily-closures.store'), [
            'bank_agent_id' => $this->unassignedAgent->id,
            'business_date' => $this->businessDate,
        ]);

        $response->assertForbidden();
    }

    public function test_operator_cannot_view_closure_of_unassigned_agent(): void
    {
        $this->actingAsJwt($this->admin);

        $this->post(route('daily-closures.store'), [
            'bank_agent_id' => $this->unassignedAgent->id,
            'business_date' => $this->businessDate,
        ]);

        $closure = \App\Modules\DailyClosing\Models\DailyClosure::first();

        $this->actingAsJwt($this->operator);

        $response = $this->get(route('daily-closures.show', $closure));
        $response->assertForbidden();
    }

    public function test_operator_index_only_shows_assigned_agents(): void
    {
        Operation::create([
            'organization_id' => $this->org->id, 'store_id' => $this->assignedAgent->store_id,
            'bank_agent_id' => $this->assignedAgent->id, 'operation_type_id' => $this->type->id,
            'user_id' => $this->operator->id, 'amount' => 100.00, 'currency' => 'PEN',
            'effective_at' => $this->businessDate . ' 10:00:00', 'recorded_at' => now(),
            'status' => Operation::STATUS_ACTIVE,
            'idempotency_key' => \Illuminate\Support\Str::random(64),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        Operation::create([
            'organization_id' => $this->org->id, 'store_id' => $this->unassignedAgent->store_id,
            'bank_agent_id' => $this->unassignedAgent->id, 'operation_type_id' => $this->type->id,
            'user_id' => $this->admin->id, 'amount' => 200.00, 'currency' => 'PEN',
            'effective_at' => $this->businessDate . ' 10:00:00', 'recorded_at' => now(),
            'status' => Operation::STATUS_ACTIVE,
            'idempotency_key' => \Illuminate\Support\Str::random(64),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->admin);
        $this->post(route('daily-closures.store'), ['bank_agent_id' => $this->assignedAgent->id, 'business_date' => $this->businessDate]);
        $this->post(route('daily-closures.store'), ['bank_agent_id' => $this->unassignedAgent->id, 'business_date' => $this->businessDate]);

        $this->actingAsJwt($this->operator);

        $response = $this->get(route('daily-closures.index'));
        $response->assertOk();
        $response->assertSee($this->assignedAgent->code);
        $response->assertDontSee($this->unassignedAgent->code);
    }
}
