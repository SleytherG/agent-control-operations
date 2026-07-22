<?php

namespace Tests\Feature\Reporting;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperatorDashboardEmptyTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_with_no_operations_sees_empty_state(): void
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
        $response->assertSee('Sin operaciones por ahora');
        $response->assertDontSee('typeDistributionChart');
        $response->assertDontSee('timeEvolutionChart');
        $response->assertDontSee('Monto bruto operado');
    }

    public function test_empty_state_no_graphic_elements_rendered(): void
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
        $response->assertSee('Sin operaciones por ahora');
        $response->assertDontSee('dashboard-cards');
    }
}
