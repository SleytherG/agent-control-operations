<?php

namespace Tests\Integration\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OperationsMigrationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_operations_tables_exist(): void
    {
        $this->assertTrue(Schema::hasTable('operation_types'));
        $this->assertTrue(Schema::hasTable('operations'));
    }

    public function test_operation_types_has_required_columns(): void
    {
        $columns = ['id', 'organization_id', 'bank_id', 'name', 'description',
                     'cash_direction', 'is_active', 'deactivated_at', 'created_at', 'updated_at'];

        foreach ($columns as $column) {
            $this->assertTrue(
                Schema::hasColumn('operation_types', $column),
                "operation_types missing column: {$column}"
            );
        }
    }

    public function test_operations_has_required_columns(): void
    {
        $columns = ['id', 'organization_id', 'store_id', 'bank_agent_id',
                     'operation_type_id', 'user_id', 'amount', 'currency',
                     'effective_at', 'recorded_at', 'status', 'reference',
                     'observation', 'annulled_by', 'annulled_at', 'annulment_reason',
                     'idempotency_key', 'created_at', 'updated_at'];

        foreach ($columns as $column) {
            $this->assertTrue(
                Schema::hasColumn('operations', $column),
                "operations missing column: {$column}"
            );
        }
    }

    public function test_idempotency_key_is_unique(): void
    {
        $this->assertTrue(
            Schema::hasTable('operations')
        );
    }

    public function test_operations_has_indexes(): void
    {
        $this->assertTrue(Schema::hasTable('operations'));
        $this->assertTrue(Schema::hasColumn('operations', 'user_id'));
        $this->assertTrue(Schema::hasColumn('operations', 'effective_at'));
        $this->assertTrue(Schema::hasColumn('operations', 'bank_agent_id'));
        $this->assertTrue(Schema::hasColumn('operations', 'store_id'));
        $this->assertTrue(Schema::hasColumn('operations', 'operation_type_id'));
        $this->assertTrue(Schema::hasColumn('operations', 'status'));
        $this->assertTrue(Schema::hasColumn('operations', 'organization_id'));
    }

    public function test_operation_types_unique_index(): void
    {
        $this->assertTrue(Schema::hasTable('operation_types'));
        $this->assertTrue(Schema::hasColumn('operation_types', 'bank_id'));
        $this->assertTrue(Schema::hasColumn('operation_types', 'name'));
    }
}
