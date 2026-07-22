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

class OperationAdminAnnulmentTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $admin;
    private Operation $oldOperation;

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

        $operator = User::factory()->create([
            'organization_id' => $this->org->id, 'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE, 'password_changed_at' => now(),
        ]);

        $region = Region::create(['organization_id' => $this->org->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $province = Province::create(['organization_id' => $this->org->id, 'region_id' => $region->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $district = District::create(['organization_id' => $this->org->id, 'province_id' => $province->id, 'name' => 'Miraflores', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $store = Store::create(['organization_id' => $this->org->id, 'district_id' => $district->id, 'code' => 'ST-001', 'name' => 'Test Store', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $bank = Bank::create(['organization_id' => $this->org->id, 'code' => 'BCP', 'name' => 'BCP', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $agent = BankAgent::create(['organization_id' => $this->org->id, 'store_id' => $store->id, 'bank_id' => $bank->id, 'code' => 'AG-001', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $type = OperationType::create([
            'organization_id' => $this->org->id, 'bank_id' => null,
            'name' => 'Depósito', 'cash_direction' => 'ENTRADA', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->oldOperation = Operation::create([
            'organization_id' => $this->org->id, 'store_id' => $store->id,
            'bank_agent_id' => $agent->id, 'operation_type_id' => $type->id,
            'user_id' => $operator->id, 'amount' => 500.00, 'currency' => 'PEN',
            'effective_at' => now()->subHours(48), 'recorded_at' => now()->subHours(48),
            'status' => 'ACTIVE', 'idempotency_key' => hash('sha256', 'admin-annul-test'),
            'created_at' => now()->subHours(48), 'updated_at' => now()->subHours(48),
        ]);
    }

    public function test_admin_can_annul_operation_beyond_operator_window(): void
    {
        $this->actingAsJwt($this->admin);

        $response = $this->post(route('operations.annul', $this->oldOperation), [
            'reason' => 'Anulación correctiva por administrador',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('operations', [
            'id' => $this->oldOperation->id,
            'status' => 'ANNULLED',
            'annulled_by' => $this->admin->id,
        ]);
    }
}
