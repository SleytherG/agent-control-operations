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

class OperationsQuickstartTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_operations_workflow(): void
    {
        $org = Organization::factory()->create();

        $admin = User::factory()->create([
            'organization_id' => $org->id,
            'role' => Role::ADMINISTRADOR_PROPIETARIO,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);

        $operator = User::factory()->create([
            'organization_id' => $org->id,
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);

        $region = Region::create(['organization_id' => $org->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $province = Province::create(['organization_id' => $org->id, 'region_id' => $region->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $district = District::create(['organization_id' => $org->id, 'province_id' => $province->id, 'name' => 'Miraflores', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $store = Store::create(['organization_id' => $org->id, 'district_id' => $district->id, 'code' => 'ST-001', 'name' => 'Tienda Test', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $bank = Bank::create(['organization_id' => $org->id, 'code' => 'BCP', 'name' => 'Banco de Crédito', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $agent = BankAgent::create(['organization_id' => $org->id, 'store_id' => $store->id, 'bank_id' => $bank->id, 'code' => 'AG-001', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        UserBankAgentAssignment::create([
            'user_id' => $operator->id, 'bank_agent_id' => $agent->id,
            'assigned_by' => $admin->id, 'assigned_at' => now(),
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);

        // Admin creates operation types (US1)
        $this->actingAsJwt($admin);

        $this->post(route('admin.operation-types.store'), [
            'name' => 'Depósito',
            'cash_direction' => 'ENTRADA',
            'bank_id' => null,
        ]);

        $this->post(route('admin.operation-types.store'), [
            'name' => 'Retiro',
            'cash_direction' => 'SALIDA',
            'bank_id' => null,
        ]);

        $depositType = OperationType::where('name', 'Depósito')->first();
        $withdrawalType = OperationType::where('name', 'Retiro')->first();

        $this->assertNotNull($depositType);
        $this->assertNotNull($withdrawalType);

        // Operator registers operations (US2)
        $this->actingAsJwt($operator);

        $idempotencyKey = Str::random(64);

        $response = $this->post(route('operations.store'), [
            'bank_agent_id' => $agent->id,
            'operation_type_id' => $depositType->id,
            'amount' => 500.75,
            'currency' => 'PEN',
            'effective_at' => now()->format('Y-m-d H:i:s'),
            'idempotency_key' => $idempotencyKey,
            'reference' => 'REF-001',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('operations', [
            'amount' => 500.75,
            'user_id' => $operator->id,
            'status' => 'ACTIVE',
        ]);

        $operation = Operation::first();

        // Idempotency: duplicate submission returns existing (US2)
        $dupResponse = $this->post(route('operations.store'), [
            'bank_agent_id' => $agent->id,
            'operation_type_id' => $depositType->id,
            'amount' => 999.99,
            'effective_at' => now()->format('Y-m-d H:i:s'),
            'idempotency_key' => $idempotencyKey,
        ]);

        $dupResponse->assertRedirect();
        $dupResponse->assertSessionHas('idempotent', true);
        $this->assertEquals(1, Operation::count());

        // Operator views history (US3)
        $response = $this->get(route('operations.index'));
        $response->assertStatus(200);
        $response->assertSee('500.75');

        $response = $this->get(route('operations.show', $operation));
        $response->assertStatus(200);

        // Operator annuls own operation (US4)
        $annulResponse = $this->post(route('operations.annul', $operation), [
            'reason' => 'Error de digitación',
        ]);

        $annulResponse->assertRedirect();
        $annulResponse->assertSessionHas('status');

        $this->assertDatabaseHas('operations', [
            'id' => $operation->id,
            'status' => 'ANNULLED',
            'annulled_by' => $operator->id,
            'annulment_reason' => 'Error de digitación',
        ]);

        // Annulled operation visible in history (US3)
        $response = $this->get(route('operations.index'));
        $response->assertSee('500.75');
        $response->assertSee('Anulada');
    }
}
