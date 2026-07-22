<?php

namespace Tests\Integration\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OperationalStructureMigrationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_operational_tables_exist(): void
    {
        $tables = [
            'regions', 'provinces', 'districts', 'stores',
            'banks', 'bank_agents', 'user_bank_agent_assignments',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Table {$table} does not exist.");
        }
    }

    public function test_users_has_password_changed_at_column(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'password_changed_at'));
    }

    public function test_migrations_can_run_up_and_down(): void
    {
        $this->assertTrue(Schema::hasTable('regions'));
        $this->assertTrue(Schema::hasTable('provinces'));
        $this->assertTrue(Schema::hasTable('districts'));
        $this->assertTrue(Schema::hasTable('stores'));
        $this->assertTrue(Schema::hasTable('banks'));
        $this->assertTrue(Schema::hasTable('bank_agents'));
        $this->assertTrue(Schema::hasTable('user_bank_agent_assignments'));
    }

    public function test_foreign_keys_exist(): void
    {
        $this->assertTrue(Schema::hasTable('regions'));
        $this->assertTrue(Schema::hasTable('provinces'));
        $this->assertTrue(Schema::hasTable('districts'));

        $this->assertTrue(Schema::hasColumn('stores', 'district_id'));
        $this->assertTrue(Schema::hasColumn('stores', 'organization_id'));

        $this->assertTrue(Schema::hasColumn('bank_agents', 'store_id'));
        $this->assertTrue(Schema::hasColumn('bank_agents', 'bank_id'));
        $this->assertTrue(Schema::hasColumn('bank_agents', 'organization_id'));

        $this->assertTrue(Schema::hasColumn('user_bank_agent_assignments', 'user_id'));
        $this->assertTrue(Schema::hasColumn('user_bank_agent_assignments', 'bank_agent_id'));
        $this->assertTrue(Schema::hasColumn('user_bank_agent_assignments', 'assigned_by'));
    }
}
