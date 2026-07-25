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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationValidationTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $operator;
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
        $this->operator = User::factory()->create([
            'organization_id' => $this->org->id,
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE, 'password_changed_at' => now(),
        ]);
        $this->agent = Agent::create([
            'organization_id' => $this->org->id, 'code' => 'AG-VAL', 'name' => 'Validation Agent',
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

    public function test_negative_amount_rejected(): void
    {
        $this->actingAsJwt($this->operator);
        $this->post(route('operations.store'), [
            'agent_id' => $this->agent->id,
            'operation_type_id' => $this->type->id,
            'amount' => -50,
            'effective_at' => now()->format('Y-m-d H:i:s'),
            'idempotency_key' => hash('sha256', 'neg-1'),
        ])->assertSessionHasErrors(['amount']);
    }

    public function test_zero_amount_rejected(): void
    {
        $this->actingAsJwt($this->operator);
        $this->post(route('operations.store'), [
            'agent_id' => $this->agent->id,
            'operation_type_id' => $this->type->id,
            'amount' => 0,
            'effective_at' => now()->format('Y-m-d H:i:s'),
            'idempotency_key' => hash('sha256', 'zero-1'),
        ])->assertSessionHasErrors(['amount']);
    }

    public function test_inactive_type_rejected(): void
    {
        $inactiveType = OperationType::create([
            'organization_id' => $this->org->id, 'name' => 'Inactive Type',
            'cash_multiplier' => 0, 'digital_multiplier' => 0,
            'is_active' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->operator);
        $this->post(route('operations.store'), [
            'agent_id' => $this->agent->id,
            'operation_type_id' => $inactiveType->id,
            'amount' => 100,
            'effective_at' => now()->format('Y-m-d H:i:s'),
            'idempotency_key' => hash('sha256', 'inact-1'),
        ])->assertSessionHasErrors(['operation_type_id']);
    }

    public function test_operator_without_assignment_rejected(): void
    {
        $noAssignOp = User::factory()->create([
            'organization_id' => $this->org->id,
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE, 'password_changed_at' => now(),
        ]);

        $this->actingAsJwt($noAssignOp);
        $this->post(route('operations.store'), [
            'agent_id' => $this->agent->id,
            'operation_type_id' => $this->type->id,
            'amount' => 100,
            'effective_at' => now()->format('Y-m-d H:i:s'),
            'idempotency_key' => hash('sha256', 'noassign-1'),
        ])->assertStatus(500);
    }
}
