<?php

namespace Tests\Feature\Migration;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Agents\Models\Agent;
use App\Modules\Operations\Models\OperationType;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationsMigrationTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $admin;
    private User $operator;
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
        $this->type = OperationType::create([
            'organization_id' => $this->org->id, 'name' => 'Depósito Mig',
            'cash_multiplier' => 1, 'digital_multiplier' => 0,
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_data_migration_populates_agent_id_and_internal_code(): void
    {
        $agent = Agent::create([
            'organization_id' => $this->org->id, 'code' => 'AG-MIG', 'name' => 'Migration Agent',
            'city' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);

        \DB::table('_migration_map')->insert([
            'old_table' => 'bank_agents', 'old_id' => 99, 'new_agent_id' => $agent->id,
            'created_at' => now(),
        ]);

        $op = \App\Modules\Operations\Models\Operation::create([
            'organization_id' => $this->org->id,
            'agent_id' => null,
            'bank_agent_id' => 99,
            'operation_type_id' => $this->type->id,
            'user_id' => $this->operator->id,
            'amount' => 100,
            'currency' => 'PEN',
            'effective_at' => now(),
            'recorded_at' => now(),
            'status' => 'ACTIVE',
            'idempotency_key' => hash('sha256', 'mig-test-' . uniqid()),
            'internal_code' => null,
            'cash_delta' => 0,
            'digital_delta' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertNull($op->agent_id);
        $this->assertNull($op->internal_code);

        $migration = include database_path('migrations/2026_07_23_000006_migrate_operations_data.php');
        (new $migration())->up();

        $op->refresh();
        $this->assertNotNull($op->agent_id, 'agent_id should be populated after migration');
        $this->assertEquals('OP-LEGACY-' . $op->id, $op->internal_code);
        $this->assertEquals(100.00, (float) $op->cash_delta);
    }
}
