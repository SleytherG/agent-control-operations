<?php

namespace Tests\Feature\Reporting;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardEmptyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_with_no_operations_sees_empty_state(): void
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
        $response->assertSee('Sin resultados');
        $response->assertDontSee('Monto bruto operado');
        $response->assertDontSee('typeDistributionChart');
    }

    public function test_admin_filter_with_no_results_shows_empty_state(): void
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create([
            'organization_id' => $org->id,
            'role' => Role::ADMINISTRADOR_PROPIETARIO,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);

        $this->actingAsJwt($admin);

        $response = $this->get(route('admin.dashboard', [
            'period' => 'day',
            'date_from' => '2020-01-01',
            'date_to' => '2020-01-02',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Sin resultados');
        $response->assertDontSee('Monto bruto operado');
    }
}
