<?php

namespace Tests\Performance;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Agents\Models\Agent;
use App\Modules\Operations\Models\Operation;
use App\Modules\Operations\Models\OperationType;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_within_performance_target(): void
    {
        $org = Organization::factory()->create();

        $admin = User::factory()->create([
            'organization_id' => $org->id,
            'role' => Role::ADMINISTRADOR_PROPIETARIO,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);

        $agent = Agent::create([
            'organization_id' => $org->id, 'code' => 'AG-PERF', 'name' => 'Perf Agent',
            'city' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $type = OperationType::create([
            'organization_id' => $org->id, 'name' => 'Depósito',
            'cash_multiplier' => 1, 'digital_multiplier' => 0,
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $start = microtime(true);

        for ($i = 0; $i < 100; $i++) {
            Operation::create([
                'organization_id' => $org->id, 'agent_id' => $agent->id,
                'operation_type_id' => $type->id, 'user_id' => $admin->id,
                'amount' => rand(10, 500), 'currency' => 'PEN',
                'effective_at' => now()->subDays(rand(0, 30)),
                'recorded_at' => now()->subDays(rand(0, 30)),
                'status' => 'ACTIVE',
                'idempotency_key' => hash('sha256', 'perf-' . $i . '-' . uniqid()),
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $insertTime = round(microtime(true) - $start, 3);

        $this->actingAsJwt($admin);

        $renderStart = microtime(true);
        $response = $this->get(route('admin.dashboard'));
        $renderTime = round(microtime(true) - $renderStart, 3);

        $response->assertOk();

        $this->assertLessThan(
            3.0,
            $renderTime,
            "Admin dashboard render time ({$renderTime}s) exceeds 3s limit"
        );

        $this->assertLessThan(
            60.0,
            $insertTime,
            "100 operation inserts ({$insertTime}s) should complete reasonably"
        );
    }
}
