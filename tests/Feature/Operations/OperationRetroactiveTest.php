<?php

namespace Tests\Feature\Operations;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Agents\Models\Agent;
use App\Modules\Agents\Models\UserAgentAssignment;
use App\Modules\Operations\Models\OperationType;
use App\Modules\Operations\Models\Operation;
use App\Modules\Organization\Models\Organization;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationRetroactiveTest extends TestCase
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
            'organization_id' => $this->org->id, 'code' => 'AG-RETRO', 'name' => 'Retroactive Agent',
            'city' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        UserAgentAssignment::create([
            'user_id' => $this->operator->id, 'agent_id' => $this->agent->id,
            'assigned_by' => $this->admin->id, 'starts_at' => now(),
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        UserAgentAssignment::create([
            'user_id' => $this->admin->id, 'agent_id' => $this->agent->id,
            'assigned_by' => $this->admin->id, 'starts_at' => now(),
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->type = OperationType::create([
            'organization_id' => $this->org->id, 'name' => 'Depósito',
            'cash_multiplier' => 1, 'digital_multiplier' => 0,
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_operator_cannot_use_past_date(): void
    {
        $pastDate = now()->subHours(3)->format('Y-m-d H:i:s');

        $this->actingAsJwt($this->operator);
        $this->post(route('operations.store'), [
            'agent_id' => $this->agent->id,
            'operation_type_id' => $this->type->id,
            'amount' => 100,
            'effective_at' => $pastDate,
            'idempotency_key' => hash('sha256', 'retro-op-' . uniqid()),
        ]);

        $op = Operation::orderBy('id', 'desc')->first();
        if ($op) {
            $this->assertTrue(
                Carbon::parse($op->effective_at)->diffInSeconds(now()) < 5,
                'Operator should always use server now(), not a past date'
            );
        }
    }

    public function test_admin_can_register_within_24h_window(): void
    {
        $pastDate = now()->subHours(2)->format('Y-m-d H:i:s');

        $this->actingAsJwt($this->admin);
        $response = $this->post(route('operations.store'), [
            'agent_id' => $this->agent->id,
            'operation_type_id' => $this->type->id,
            'amount' => 100,
            'effective_at' => $pastDate,
            'idempotency_key' => hash('sha256', 'adm-retro-' . uniqid()),
        ]);

        $response->assertRedirect(route('operations.index'));
        $this->assertDatabaseHas('operations', ['amount' => '100.00']);
    }
}
