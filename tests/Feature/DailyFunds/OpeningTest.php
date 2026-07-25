<?php

namespace Tests\Feature\DailyFunds;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Agents\Models\Agent;
use App\Modules\Agents\Models\UserAgentAssignment;
use App\Modules\DailyClosing\Models\DailyClosure;
use App\Modules\Operations\Models\Operation;
use App\Modules\Operations\Models\OperationType;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpeningTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $admin;
    private Agent $agent;

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
        $this->agent = Agent::create([
            'organization_id' => $this->org->id,
            'code' => 'AG-OPN', 'name' => 'Opening Agent', 'city' => 'Lima',
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_admin_can_open_day_with_initial_balances(): void
    {
        $this->actingAsJwt($this->admin);

        $this->post(route('daily-closures.store'), [
            'agent_id' => $this->agent->id,
            'business_date' => now()->format('Y-m-d'),
            'opening_cash' => 500.00,
            'opening_digital' => 300.00,
        ])->assertRedirect();

        $this->assertDatabaseHas('daily_closures', [
            'agent_id' => $this->agent->id,
            'opening_cash' => '500.00',
            'opening_digital' => '300.00',
            'status' => 'BORRADOR',
        ]);
    }

    public function test_cannot_open_duplicate_active_day(): void
    {
        DailyClosure::create([
            'organization_id' => $this->org->id,
            'agent_id' => $this->agent->id,
            'business_date' => now()->format('Y-m-d'),
            'status' => DailyClosure::STATUS_ACTIVO,
            'opening_cash' => 100,
            'opening_digital' => 50,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->admin);

        $this->post(route('daily-closures.store'), [
            'agent_id' => $this->agent->id,
            'business_date' => now()->format('Y-m-d'),
            'opening_cash' => 200,
            'opening_digital' => 100,
        ])->assertRedirect();

        $this->assertEquals(1, DailyClosure::where('agent_id', $this->agent->id)
            ->whereDate('business_date', now()->format('Y-m-d'))
            ->whereIn('status', [DailyClosure::STATUS_ACTIVO, 'BORRADOR'])
            ->count());
    }
}
