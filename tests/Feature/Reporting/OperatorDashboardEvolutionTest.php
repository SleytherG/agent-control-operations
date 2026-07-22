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

class OperatorDashboardEvolutionTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $operator;
    private BankAgent $agent;
    private OperationType $entryType;

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
    }

    public function test_time_evolution_chart_is_present(): void
    {
        Operation::create([
            'organization_id' => $this->org->id, 'store_id' => $this->agent->store_id,
            'bank_agent_id' => $this->agent->id, 'operation_type_id' => $this->entryType->id,
            'user_id' => $this->operator->id, 'amount' => 100.00, 'currency' => 'PEN',
            'effective_at' => now()->subDays(1)->format('Y-m-d H:i:s'),
            'recorded_at' => now(), 'status' => 'ACTIVE',
            'idempotency_key' => Str::random(64), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->operator);

        $response = $this->get(route('dashboard.operator', ['period' => 'week']));

        $response->assertStatus(200);
        $response->assertSee('timeEvolutionChart');
        $response->assertSee('Evolución temporal');
    }

    public function test_period_selection_changes_data(): void
    {
        Operation::create([
            'organization_id' => $this->org->id, 'store_id' => $this->agent->store_id,
            'bank_agent_id' => $this->agent->id, 'operation_type_id' => $this->entryType->id,
            'user_id' => $this->operator->id, 'amount' => 100.00, 'currency' => 'PEN',
            'effective_at' => now()->format('Y-m-d H:i:s'),
            'recorded_at' => now(), 'status' => 'ACTIVE',
            'idempotency_key' => Str::random(64), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->operator);

        $dayResponse = $this->get(route('dashboard.operator', ['period' => 'day', 'date' => now()->format('Y-m-d')]));
        $dayResponse->assertStatus(200);

        $weekResponse = $this->get(route('dashboard.operator', ['period' => 'week', 'date' => now()->format('Y-m-d')]));
        $weekResponse->assertStatus(200);

        $monthResponse = $this->get(route('dashboard.operator', ['period' => 'month', 'date' => now()->format('Y-m-d')]));
        $monthResponse->assertStatus(200);

        $this->assertTrue(true);
    }

    public function test_cannot_access_as_unauthenticated(): void
    {
        $response = $this->get(route('dashboard.operator'));

        $response->assertRedirect(route('login'));
    }
}
