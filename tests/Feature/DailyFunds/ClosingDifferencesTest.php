<?php

namespace Tests\Feature\DailyFunds;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Agents\Models\Agent;
use App\Modules\DailyClosing\Models\DailyClosure;
use App\Modules\Operations\Models\Operation;
use App\Modules\Operations\Models\OperationType;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClosingDifferencesTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $admin;
    private Agent $agent;
    private OperationType $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->org = Organization::factory()->create();
        $this->admin = User::factory()->create([
            'organization_id' => $this->org->id,
            'role' => Role::ADMINISTRADOR_PROPIETARIO,
            'status' => UserStatus::ACTIVE, 'password_changed_at' => now(),
        ]);
        $this->agent = Agent::create([
            'organization_id' => $this->org->id, 'code' => 'AG-CDIFF', 'name' => 'Diff Agent',
            'city' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->type = OperationType::create([
            'organization_id' => $this->org->id, 'name' => 'Depósito',
            'cash_multiplier' => 1, 'digital_multiplier' => 0,
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_confirm_with_differences_requires_reason(): void
    {
        $closure = DailyClosure::create([
            'organization_id' => $this->org->id, 'agent_id' => $this->agent->id,
            'business_date' => now()->format('Y-m-d'), 'status' => DailyClosure::STATUS_ACTIVO,
            'opening_cash' => 100, 'opening_digital' => 0,
            'actual_closing_cash' => 200,
            'expected_closing_cash' => 100,
            'cash_difference' => 100,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->admin);
        $this->post(route('daily-closures.confirm', $closure))
            ->assertSessionHasErrors(['confirm']);
    }

    public function test_confirm_with_differences_and_reason_succeeds(): void
    {
        $closure = DailyClosure::create([
            'organization_id' => $this->org->id, 'agent_id' => $this->agent->id,
            'business_date' => now()->format('Y-m-d'), 'status' => DailyClosure::STATUS_ACTIVO,
            'opening_cash' => 100, 'opening_digital' => 0,
            'actual_closing_cash' => 200,
            'expected_closing_cash' => 100,
            'cash_difference' => 100,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->admin);
        $this->post(route('daily-closures.confirm', $closure), [
            'confirm_reason' => 'Sobrante justificado por depósito no registrado',
        ])->assertRedirect();

        $this->assertEquals(
            DailyClosure::STATUS_CONFIRMADO,
            $closure->fresh()->status
        );
    }
}
