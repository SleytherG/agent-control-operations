<?php

namespace Tests\Feature\Organization;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use App\Modules\Organization\Models\Region;
use App\Modules\Organization\Models\Province;
use App\Modules\Organization\Models\District;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeoHierarchyTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $admin;
    private Region $region;

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

        $this->region = Region::create([
            'organization_id' => $this->org->id,
            'name' => 'Lima',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_admin_can_create_region(): void
    {
        $this->actingAsJwt($this->admin);

        $response = $this->post(route('admin.regions.store'), ['name' => 'Arequipa']);
        $response->assertRedirect(route('admin.regions.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('regions', ['name' => 'Arequipa', 'organization_id' => $this->org->id]);
    }

    public function test_admin_can_create_province(): void
    {
        $this->actingAsJwt($this->admin);

        $response = $this->post(route('admin.regions.provinces.store', $this->region), ['name' => 'Arequipa']);
        $response->assertRedirect(route('admin.regions.provinces.index', $this->region));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('provinces', ['name' => 'Arequipa', 'region_id' => $this->region->id]);
    }

    public function test_admin_can_create_district(): void
    {
        $this->actingAsJwt($this->admin);

        $province = Province::create([
            'organization_id' => $this->org->id,
            'region_id' => $this->region->id,
            'name' => 'Lima',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post(route('admin.provinces.districts.store', $province), ['name' => 'Cercado']);
        $response->assertRedirect(route('admin.provinces.districts.index', $province));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('districts', ['name' => 'Cercado', 'province_id' => $province->id]);
    }

    public function test_admin_can_deactivate_region(): void
    {
        $this->actingAsJwt($this->admin);

        $response = $this->delete(route('admin.regions.deactivate', $this->region));
        $response->assertRedirect(route('admin.regions.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('regions', ['id' => $this->region->id, 'is_active' => false]);
        $this->assertNotNull($this->region->fresh()->deactivated_at);
    }

    public function test_admin_can_deactivate_province(): void
    {
        $this->actingAsJwt($this->admin);

        $province = Province::create([
            'organization_id' => $this->org->id,
            'region_id' => $this->region->id,
            'name' => 'Callao',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->delete(route('admin.provinces.deactivate', $province));

        $this->assertDatabaseHas('provinces', ['id' => $province->id, 'is_active' => false]);
    }

    public function test_admin_can_deactivate_district(): void
    {
        $this->actingAsJwt($this->admin);

        $province = Province::create([
            'organization_id' => $this->org->id,
            'region_id' => $this->region->id,
            'name' => 'Lima',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $district = District::create([
            'organization_id' => $this->org->id,
            'province_id' => $province->id,
            'name' => 'Test',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->delete(route('admin.districts.deactivate', $district));

        $this->assertDatabaseHas('districts', ['id' => $district->id, 'is_active' => false]);
    }

    public function test_nested_hierarchy_can_be_traversed(): void
    {
        $this->actingAsJwt($this->admin);

        $response = $this->get(route('admin.regions.show', $this->region));
        $response->assertStatus(200);
    }
}
