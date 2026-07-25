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

class ClosingAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $admin;
    private User $operator;
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
        $this->operator = User::factory()->create([
            'organization_id' => $this->org->id,
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE, 'password_changed_at' => now(),
        ]);
        $this->agent = Agent::create([
            'organization_id' => $this->org->id, 'code' => 'AG-CAUTH', 'name' => 'Closing Auth Agent',
            'city' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        UserAgentAssignment::create([
            'user_id' => $this->operator->id, 'agent_id' => $this->agent->id,
            'assigned_by' => $this->admin->id, 'starts_at' => now(),
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->type = OperationType::create([
            'organization_id' => $this->org->id, 'name' => 'Depósito',
            'cash_multiplier' => 1, 'digital_multiplier' => 0,
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_operator_cannot_confirm(): void
    {
        $closure = DailyClosure::create([
            'organization_id' => $this->org->id, 'agent_id' => $this->agent->id,
            'business_date' => now()->format('Y-m-d'), 'status' => DailyClosure::STATUS_ACTIVO,
            'opening_cash' => 100, 'opening_digital' => 50,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->operator);
        $this->post(route('daily-closures.confirm', $closure))
            ->assertForbidden();
    }

    public function test_admin_can_confirm(): void
    {
        $closure = DailyClosure::create([
            'organization_id' => $this->org->id, 'agent_id' => $this->agent->id,
            'business_date' => now()->format('Y-m-d'), 'status' => DailyClosure::STATUS_ACTIVO,
            'opening_cash' => 100, 'opening_digital' => 50,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->admin);
        $this->post(route('daily-closures.confirm', $closure))
            ->assertRedirect();

        $this->assertEquals(
            DailyClosure::STATUS_CONFIRMADO,
            $closure->fresh()->status
        );
    }

    public function test_operator_cannot_reopen(): void
    {
        $closure = DailyClosure::create([
            'organization_id' => $this->org->id, 'agent_id' => $this->agent->id,
            'business_date' => now()->format('Y-m-d'), 'status' => DailyClosure::STATUS_CONFIRMADO,
            'opening_cash' => 100, 'opening_digital' => 50,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->operator);
        $this->post(route('daily-closures.reopen', $closure), ['reason' => 'Test'])
            ->assertForbidden();
    }

    public function test_admin_can_reopen_with_reason(): void
    {
        $closure = DailyClosure::create([
            'organization_id' => $this->org->id, 'agent_id' => $this->agent->id,
            'business_date' => now()->format('Y-m-d'), 'status' => DailyClosure::STATUS_CONFIRMADO,
            'opening_cash' => 100, 'opening_digital' => 50,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->admin);
        $this->post(route('daily-closures.reopen', $closure), ['reason' => 'Error en registro'])
            ->assertRedirect();

        $this->assertEquals(
            DailyClosure::STATUS_REABIERTO,
            $closure->fresh()->status
        );
    }
}
