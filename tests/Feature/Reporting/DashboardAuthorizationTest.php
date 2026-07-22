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

class DashboardAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_access_operator_dashboard(): void
    {
        $org = Organization::factory()->create();
        $operator = User::factory()->create([
            'organization_id' => $org->id,
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);

        $this->actingAsJwt($operator);

        $response = $this->get(route('dashboard.operator'));
        $response->assertStatus(200);
    }

    public function test_admin_can_access_operator_dashboard(): void
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create([
            'organization_id' => $org->id,
            'role' => Role::ADMINISTRADOR_PROPIETARIO,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);

        $this->actingAsJwt($admin);

        $response = $this->get(route('dashboard.operator'));
        $response->assertStatus(200);
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create([
            'organization_id' => $org->id,
            'role' => Role::ADMINISTRADOR_PROPIETARIO,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);

        $this->actingAsJwt($admin);

        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(200);
    }

    public function test_operator_cannot_access_admin_dashboard(): void
    {
        $org = Organization::factory()->create();
        $operator = User::factory()->create([
            'organization_id' => $org->id,
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);

        $this->actingAsJwt($operator);

        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    public function test_operator_cannot_access_operator_comparison(): void
    {
        $org = Organization::factory()->create();
        $operator = User::factory()->create([
            'organization_id' => $org->id,
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);

        $this->actingAsJwt($operator);

        $response = $this->get(route('admin.dashboard.operators'));
        $response->assertStatus(403);
    }

    public function test_admin_can_access_operator_comparison(): void
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create([
            'organization_id' => $org->id,
            'role' => Role::ADMINISTRADOR_PROPIETARIO,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);

        $this->actingAsJwt($admin);

        $response = $this->get(route('admin.dashboard.operators'));
        $response->assertStatus(200);
    }

    public function test_unauthenticated_cannot_access_dashboard(): void
    {
        $response = $this->get(route('dashboard.operator'));
        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_cannot_access_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_cannot_access_comparison(): void
    {
        $response = $this->get(route('admin.dashboard.operators'));
        $response->assertRedirect(route('login'));
    }

    public function test_monto_bruto_operado_label_present_in_all_views(): void
    {
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

        Operation::create([
            'organization_id' => $org->id, 'store_id' => $store->id, 'bank_agent_id' => $agent->id,
            'operation_type_id' => $type->id, 'user_id' => $operator->id,
            'amount' => 100.00, 'currency' => 'PEN',
            'effective_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'recorded_at' => now(), 'status' => 'ACTIVE',
            'idempotency_key' => Str::random(64), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAsJwt($operator);
        $opResponse = $this->get(route('dashboard.operator'));
        $opResponse->assertStatus(200);
        $opResponse->assertSee('Monto bruto operado');
        $opResponse->assertDontSee('Ingreso');
        $opResponse->assertDontSee('Utilidad');
        $opResponse->assertDontSee('Ganancia');

        $this->actingAsJwt($admin);
        $adResponse = $this->get(route('admin.dashboard'));
        $adResponse->assertStatus(200);
        $adResponse->assertSee('Monto bruto operado');
        $adResponse->assertDontSee('Ingreso');
        $adResponse->assertDontSee('Utilidad');
        $adResponse->assertDontSee('Ganancia');

        $cmpResponse = $this->get(route('admin.dashboard.operators'));
        $cmpResponse->assertStatus(200);
        $cmpResponse->assertSee('Monto bruto operado');
        $cmpResponse->assertDontSee('Ingreso');
        $cmpResponse->assertDontSee('Utilidad');
        $cmpResponse->assertDontSee('Ganancia');
    }
}
