<?php

namespace Tests\Feature\BankingNetwork;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\District;
use App\Modules\Organization\Models\Province;
use App\Modules\Organization\Models\Region;
use App\Modules\Organization\Models\Organization;
use App\Modules\Organization\Models\Store;
use App\Modules\BankingNetwork\Models\Bank;
use App\Modules\BankingNetwork\Models\BankAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankAgentCreateTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $admin;
    private Store $store;
    private Bank $bank;

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

        $region = Region::create([
            'organization_id' => $this->org->id, 'name' => 'Lima', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $province = Province::create([
            'organization_id' => $this->org->id, 'region_id' => $region->id, 'name' => 'Lima',
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $district = District::create([
            'organization_id' => $this->org->id, 'province_id' => $province->id, 'name' => 'Miraflores',
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->store = Store::create([
            'organization_id' => $this->org->id, 'district_id' => $district->id,
            'code' => 'ST-001', 'name' => 'Tienda Test', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->bank = Bank::create([
            'organization_id' => $this->org->id, 'code' => 'BCP', 'name' => 'BCP',
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_admin_can_create_bank_agent(): void
    {
        $this->actingAsJwt($this->admin);

        $response = $this->post(route('admin.bank-agents.store'), [
            'store_id' => $this->store->id,
            'bank_id' => $this->bank->id,
            'code' => 'AG-001',
            'terminal_code' => 'TERM-001',
        ]);

        $response->assertRedirect(route('admin.bank-agents.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('bank_agents', [
            'organization_id' => $this->org->id,
            'store_id' => $this->store->id,
            'bank_id' => $this->bank->id,
            'code' => 'AG-001',
            'is_active' => true,
        ]);
    }

    public function test_bank_agent_rejects_inactive_store(): void
    {
        $this->actingAsJwt($this->admin);

        $inactiveStore = Store::create([
            'organization_id' => $this->org->id, 'district_id' => $this->store->district_id,
            'code' => 'ST-INACTIVE', 'name' => 'Inactiva', 'is_active' => false,
            'deactivated_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->post(route('admin.bank-agents.store'), [
            'store_id' => $inactiveStore->id,
            'bank_id' => $this->bank->id,
            'code' => 'AG-002',
        ]);

        $response->assertSessionHasErrors('store_id');
    }

    public function test_bank_agent_rejects_inactive_bank(): void
    {
        $this->actingAsJwt($this->admin);

        $inactiveBank = Bank::create([
            'organization_id' => $this->org->id, 'code' => 'INACTIVE', 'name' => 'Inactivo',
            'is_active' => false, 'deactivated_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->post(route('admin.bank-agents.store'), [
            'store_id' => $this->store->id,
            'bank_id' => $inactiveBank->id,
            'code' => 'AG-003',
        ]);

        $response->assertSessionHasErrors('bank_id');
    }

    public function test_operator_cannot_create_bank_agent(): void
    {
        $operator = User::factory()->create([
            'organization_id' => $this->org->id,
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);
        $this->actingAsJwt($operator);

        $response = $this->post(route('admin.bank-agents.store'), [
            'store_id' => $this->store->id,
            'bank_id' => $this->bank->id,
            'code' => 'AG-999',
        ]);

        $response->assertForbidden();
    }
}
