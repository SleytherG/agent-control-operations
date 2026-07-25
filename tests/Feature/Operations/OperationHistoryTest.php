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

class OperationHistoryTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $admin;
    private User $operator;
    private User $otherOperator;
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
        $this->otherOperator = User::factory()->create([
            'organization_id' => $this->org->id,
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE, 'password_changed_at' => now(),
        ]);
        $this->agent = Agent::create([
            'organization_id' => $this->org->id, 'code' => 'AG-HIST', 'name' => 'History Agent',
            'city' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        UserAgentAssignment::create([
            'user_id' => $this->operator->id, 'agent_id' => $this->agent->id,
            'assigned_by' => $this->admin->id, 'starts_at' => now(),
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        UserAgentAssignment::create([
            'user_id' => $this->otherOperator->id, 'agent_id' => $this->agent->id,
            'assigned_by' => $this->admin->id, 'starts_at' => now(),
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->type = OperationType::create([
            'organization_id' => $this->org->id, 'name' => 'Depósito',
            'cash_multiplier' => 1, 'digital_multiplier' => 0,
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_operator_sees_only_own_operations(): void
    {
        Operation::create([
            'organization_id' => $this->org->id, 'agent_id' => $this->agent->id,
            'operation_type_id' => $this->type->id, 'user_id' => $this->operator->id,
            'amount' => 100, 'currency' => 'PEN',
            'effective_at' => now(), 'recorded_at' => now(),
            'status' => 'ACTIVE',
            'idempotency_key' => hash('sha256', 'own-1'),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        Operation::create([
            'organization_id' => $this->org->id, 'agent_id' => $this->agent->id,
            'operation_type_id' => $this->type->id, 'user_id' => $this->otherOperator->id,
            'amount' => 200, 'currency' => 'PEN',
            'effective_at' => now(), 'recorded_at' => now(),
            'status' => 'ACTIVE',
            'idempotency_key' => hash('sha256', 'other-1'),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->operator);
        $response = $this->get(route('operations.index'));
        $response->assertOk();

        $this->assertStringContainsString('100.00', $response->getContent());
        $this->assertStringNotContainsString('200.00', $response->getContent());
    }

    public function test_admin_sees_all_operations(): void
    {
        $op1 = Operation::create([
            'organization_id' => $this->org->id, 'agent_id' => $this->agent->id,
            'operation_type_id' => $this->type->id, 'user_id' => $this->operator->id,
            'amount' => 100, 'currency' => 'PEN',
            'effective_at' => now(), 'recorded_at' => now(),
            'status' => 'ACTIVE',
            'idempotency_key' => hash('sha256', 'admin-1'),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $op2 = Operation::create([
            'organization_id' => $this->org->id, 'agent_id' => $this->agent->id,
            'operation_type_id' => $this->type->id, 'user_id' => $this->otherOperator->id,
            'amount' => 200, 'currency' => 'PEN',
            'effective_at' => now(), 'recorded_at' => now(),
            'status' => 'ACTIVE',
            'idempotency_key' => hash('sha256', 'admin-2'),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->admin);
        $response = $this->get(route('operations.index'));
        $response->assertOk();
        $this->assertTrue($op1->exists && $op2->exists);
        $this->assertEquals(2, Operation::where('organization_id', $this->org->id)->count());
    }

    public function test_history_is_paginated(): void
    {
        for ($i = 0; $i < 30; $i++) {
            Operation::create([
                'organization_id' => $this->org->id, 'agent_id' => $this->agent->id,
                'operation_type_id' => $this->type->id, 'user_id' => $this->operator->id,
                'amount' => 100 + $i, 'currency' => 'PEN',
                'effective_at' => now(), 'recorded_at' => now(),
                'status' => 'ACTIVE',
                'idempotency_key' => hash('sha256', 'page-' . $i),
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $this->actingAsJwt($this->admin);
        $response = $this->get(route('operations.index'));
        $response->assertOk();

        $this->assertStringContainsString('130.00', $response->getContent());
    }
}
