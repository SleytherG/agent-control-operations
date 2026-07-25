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

class ClosingTest extends TestCase
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
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);
        $this->operator = User::factory()->create([
            'organization_id' => $this->org->id,
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);
        $this->agent = Agent::create([
            'organization_id' => $this->org->id, 'code' => 'AG-CLS', 'name' => 'Closing Agent',
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

    public function test_closing_calculates_expected_balances(): void
    {
        $closure = DailyClosure::create([
            'organization_id' => $this->org->id, 'agent_id' => $this->agent->id,
            'business_date' => now()->format('Y-m-d'), 'status' => 'BORRADOR',
            'opening_cash' => 1000, 'opening_digital' => 500,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        Operation::create([
            'organization_id' => $this->org->id, 'agent_id' => $this->agent->id,
            'operation_type_id' => $this->type->id, 'user_id' => $this->operator->id,
            'amount' => 200, 'cash_delta' => 200, 'digital_delta' => 0,
            'currency' => 'PEN', 'effective_at' => now(), 'recorded_at' => now(),
            'status' => 'ACTIVE', 'idempotency_key' => hash('sha256', 'cls-1'),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        Operation::create([
            'organization_id' => $this->org->id, 'agent_id' => $this->agent->id,
            'operation_type_id' => $this->type->id, 'user_id' => $this->operator->id,
            'amount' => 100, 'cash_delta' => -100, 'digital_delta' => 0,
            'currency' => 'PEN', 'effective_at' => now(), 'recorded_at' => now(),
            'status' => 'ACTIVE', 'idempotency_key' => hash('sha256', 'cls-2'),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $calculator = new \App\Modules\DailyClosing\Application\Actions\CalculateClosing();
        $calculator->execute($closure);

        $closure->refresh();
        $this->assertEquals(1000 + 200 - 100, (float) $closure->expected_closing_cash);
        $this->assertEquals(500, (float) $closure->expected_closing_digital);
    }

    public function test_closing_precision_with_centavos(): void
    {
        $closure = DailyClosure::create([
            'organization_id' => $this->org->id, 'agent_id' => $this->agent->id,
            'business_date' => now()->format('Y-m-d'), 'status' => 'BORRADOR',
            'opening_cash' => 123.45, 'opening_digital' => 67.89,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        Operation::create([
            'organization_id' => $this->org->id, 'agent_id' => $this->agent->id,
            'operation_type_id' => $this->type->id, 'user_id' => $this->operator->id,
            'amount' => 99.99, 'cash_delta' => 99.99, 'digital_delta' => 0,
            'currency' => 'PEN', 'effective_at' => now(), 'recorded_at' => now(),
            'status' => 'ACTIVE', 'idempotency_key' => hash('sha256', 'prec-1'),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        Operation::create([
            'organization_id' => $this->org->id, 'agent_id' => $this->agent->id,
            'operation_type_id' => $this->type->id, 'user_id' => $this->operator->id,
            'amount' => 50.55, 'cash_delta' => -50.55, 'digital_delta' => 0,
            'currency' => 'PEN', 'effective_at' => now(), 'recorded_at' => now(),
            'status' => 'ACTIVE', 'idempotency_key' => hash('sha256', 'prec-2'),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $calculator = new \App\Modules\DailyClosing\Application\Actions\CalculateClosing();
        $calculator->execute($closure);

        $closure->refresh();

        $expectedCash = round(123.45 + 99.99 - 50.55, 2);
        $this->assertEquals($expectedCash, round((float) $closure->expected_closing_cash, 2));
        $this->assertEquals(67.89, round((float) $closure->expected_closing_digital, 2));
    }
}
