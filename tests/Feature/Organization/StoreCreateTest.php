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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreCreateTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $admin;
    private District $district;

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
            'organization_id' => $this->org->id,
            'name' => 'Lima',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $province = Province::create([
            'organization_id' => $this->org->id,
            'region_id' => $region->id,
            'name' => 'Lima',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->district = District::create([
            'organization_id' => $this->org->id,
            'province_id' => $province->id,
            'name' => 'Miraflores',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_admin_can_create_store(): void
    {
        $this->actingAsJwt($this->admin);

        $response = $this->post(route('admin.stores.store'), [
            'district_id' => $this->district->id,
            'code' => 'ST-001',
            'name' => 'Tienda Test',
            'address' => 'Av. Test 123',
        ]);

        $response->assertRedirect(route('admin.stores.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('stores', [
            'organization_id' => $this->org->id,
            'code' => 'ST-001',
            'name' => 'Tienda Test',
            'is_active' => true,
        ]);
    }

    public function test_store_requires_active_district(): void
    {
        $this->actingAsJwt($this->admin);

        $inactiveDistrict = District::create([
            'organization_id' => $this->org->id,
            'province_id' => $this->district->province_id,
            'name' => 'Inactivo',
            'is_active' => false,
            'deactivated_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post(route('admin.stores.store'), [
            'district_id' => $inactiveDistrict->id,
            'code' => 'ST-002',
            'name' => 'Tienda Test',
        ]);

        $response->assertSessionHasErrors('district_id');
    }

    public function test_store_code_unique_per_org(): void
    {
        $this->actingAsJwt($this->admin);

        Store::create([
            'organization_id' => $this->org->id,
            'district_id' => $this->district->id,
            'code' => 'ST-001',
            'name' => 'Existente',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post(route('admin.stores.store'), [
            'district_id' => $this->district->id,
            'code' => 'ST-001',
            'name' => 'Duplicada',
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_operator_cannot_create_store(): void
    {
        $operator = User::factory()->create([
            'organization_id' => $this->org->id,
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);
        $this->actingAsJwt($operator);

        $response = $this->post(route('admin.stores.store'), [
            'district_id' => $this->district->id,
            'code' => 'ST-999',
            'name' => 'No autorizado',
        ]);

        $response->assertForbidden();
    }
}
