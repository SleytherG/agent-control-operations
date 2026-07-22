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
use App\Modules\Audit\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreUpdateTest extends TestCase
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
            'code' => 'ST-001', 'name' => 'Tienda Original', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_admin_can_update_store(): void
    {
        $this->actingAsJwt($this->admin);

        $response = $this->patch(route('admin.stores.update', $this->store), [
            'district_id' => $this->store->district_id,
            'code' => 'ST-002',
            'name' => 'Tienda Actualizada',
            'address' => 'Av. Nueva 456',
        ]);

        $response->assertRedirect(route('admin.stores.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('stores', [
            'id' => $this->store->id,
            'code' => 'ST-002',
            'name' => 'Tienda Actualizada',
        ]);
    }

    public function test_store_update_creates_audit_log(): void
    {
        $this->actingAsJwt($this->admin);

        $this->patch(route('admin.stores.update', $this->store), [
            'district_id' => $this->store->district_id,
            'code' => 'ST-003',
            'name' => 'Tienda Auditada',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'entity_type' => Store::class,
            'entity_id' => $this->store->id,
            'action' => 'store.updated',
        ]);
    }

    public function test_operator_cannot_update_store(): void
    {
        $operator = User::factory()->create([
            'organization_id' => $this->org->id,
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);
        $this->actingAsJwt($operator);

        $response = $this->patch(route('admin.stores.update', $this->store), [
            'district_id' => $this->store->district_id,
            'code' => 'ST-999',
            'name' => 'No autorizado',
        ]);

        $response->assertForbidden();
    }
}
