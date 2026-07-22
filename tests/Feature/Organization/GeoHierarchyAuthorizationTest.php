<?php

namespace Tests\Feature\Organization;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use App\Modules\Organization\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeoHierarchyAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_cannot_access_geo_management(): void
    {
        $org = Organization::factory()->create();
        $operator = User::factory()->create([
            'organization_id' => $org->id,
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);

        Region::create([
            'organization_id' => $org->id,
            'name' => 'Lima',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAsJwt($operator);

        $response = $this->get(route('admin.regions.index'));
        $response->assertForbidden();

        $response = $this->post(route('admin.regions.store'), ['name' => 'Test']);
        $response->assertForbidden();
    }
}
