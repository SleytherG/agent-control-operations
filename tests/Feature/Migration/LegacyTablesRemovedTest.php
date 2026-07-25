<?php

namespace Tests\Feature\Migration;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LegacyTablesRemovedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (\DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Legacy table removal migration targets MySQL only; SQLite skips it.');
        }
    }

    public function test_legacy_tables_do_not_exist(): void
    {
        $legacyTables = ['banks', 'stores', 'bank_agents', 'user_bank_agent_assignments', '_migration_map'];

        foreach ($legacyTables as $table) {
            $this->assertFalse(
                Schema::hasTable($table),
                "Legacy table '{$table}' should not exist after migration"
            );
        }
    }

    public function test_legacy_columns_removed_from_operations(): void
    {
        $columns = Schema::getColumnListing('operations');

        $this->assertNotContains('store_id', $columns, 'store_id should be removed from operations');
        $this->assertNotContains('bank_agent_id', $columns, 'bank_agent_id should be removed from operations');
    }

    public function test_legacy_columns_removed_from_daily_closures(): void
    {
        $columns = Schema::getColumnListing('daily_closures');

        $this->assertNotContains('store_id', $columns, 'store_id should be removed from daily_closures');
        $this->assertNotContains('bank_agent_id', $columns, 'bank_agent_id should be removed from daily_closures');
    }

    public function test_agent_id_is_present_in_operations(): void
    {
        $columns = Schema::getColumnListing('operations');
        $this->assertContains('agent_id', $columns);
    }

    public function test_agent_id_is_present_in_daily_closures(): void
    {
        $columns = Schema::getColumnListing('daily_closures');
        $this->assertContains('agent_id', $columns);
    }

    public function test_agents_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('agents'));
    }
}
