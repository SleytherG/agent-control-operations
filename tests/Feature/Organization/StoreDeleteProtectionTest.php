<?php

namespace Tests\Feature\Organization;

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

class StoreDeleteProtectionTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $admin;
    private Store $store;

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
            'code' => 'ST-001', 'name' => 'Tienda Con Agentes', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_cannot_deactivate_store_with_active_agents(): void
    {
        $this->actingAsJwt($this->admin);

        $bank = Bank::create([
            'organization_id' => $this->org->id, 'code' => 'BCP', 'name' => 'BCP',
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);

        BankAgent::create([
            'organization_id' => $this->org->id, 'store_id' => $this->store->id,
            'bank_id' => $bank->id, 'code' => 'AG-001', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->delete(route('admin.stores.deactivate', $this->store));

        $response->assertRedirect(route('admin.stores.index'));
        $response->assertSessionHasErrors('deactivate');

        $this->assertDatabaseHas('stores', [
            'id' => $this->store->id,
            'is_active' => true,
        ]);
    }

    public function test_can_deactivate_store_after_agents_are_deactivated(): void
    {
        $this->actingAsJwt($this->admin);

        $bank = Bank::create([
            'organization_id' => $this->org->id, 'code' => 'BCP', 'name' => 'BCP',
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $agent = BankAgent::create([
            'organization_id' => $this->org->id, 'store_id' => $this->store->id,
            'bank_id' => $bank->id, 'code' => 'AG-001', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $agent->update(['is_active' => false, 'deactivated_at' => now()]);

        $response = $this->delete(route('admin.stores.deactivate', $this->store));
        $response->assertRedirect(route('admin.stores.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('stores', [
            'id' => $this->store->id,
            'is_active' => false,
        ]);
    }
}
