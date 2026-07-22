<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ClosingMigrationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_up_creates_daily_closures_table(): void
    {
        $this->assertTrue(Schema::hasTable('daily_closures'));
        $this->assertTrue(Schema::hasColumns('daily_closures', [
            'id', 'organization_id', 'store_id', 'bank_agent_id', 'business_date',
            'status', 'operation_count', 'gross_amount', 'cash_in', 'cash_out',
            'net_movement', 'has_pending_confirm', 'confirmed_by', 'confirmed_at',
            'reopened_by', 'reopened_at', 'reopen_reason', 'created_at', 'updated_at',
        ]));
    }

    public function test_migration_up_creates_daily_closure_operations_table(): void
    {
        $this->assertTrue(Schema::hasTable('daily_closure_operations'));
        $this->assertTrue(Schema::hasColumns('daily_closure_operations', [
            'id', 'daily_closure_id', 'operation_id', 'created_at',
        ]));
    }

    public function test_migration_down_drops_tables(): void
    {
        $this->assertTrue(Schema::hasTable('daily_closures'));
        $this->assertTrue(Schema::hasTable('daily_closure_operations'));

        $migrations = app('migrator');

        $migrationFiles = glob(database_path('migrations/*_000017_create_daily_closures_table.php'));
        if (! empty($migrationFiles)) {
            $migrator = require $migrationFiles[0];
            $migrator->down();
        }

        $migrationFiles = glob(database_path('migrations/*_000018_create_daily_closure_operations_table.php'));
        if (! empty($migrationFiles)) {
            $migrator = require $migrationFiles[0];
            $migrator->down();
        }

        $this->assertFalse(Schema::hasTable('daily_closures'));
        $this->assertFalse(Schema::hasTable('daily_closure_operations'));
    }
}
