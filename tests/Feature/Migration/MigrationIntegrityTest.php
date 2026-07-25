<?php

namespace Tests\Feature\Migration;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Fixtures\MigrationFixtures;
use Tests\TestCase;

class MigrationIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_map_table_exists(): void
    {
        $this->assertTrue(
            Schema::hasTable('_migration_map'),
            'La tabla _migration_map debe existir después de ejecutar las migraciones'
        );
    }

    public function test_migration_map_table_has_required_columns(): void
    {
        $columns = Schema::getColumnListing('_migration_map');

        $this->assertContains('id', $columns);
        $this->assertContains('old_table', $columns);
        $this->assertContains('old_id', $columns);
        $this->assertContains('new_agent_id', $columns);
        $this->assertContains('notes', $columns);
        $this->assertContains('created_at', $columns);
    }

    public function test_migration_map_can_store_records(): void
    {
        $inserted = \DB::table('_migration_map')->insert([
            'old_table' => 'stores',
            'old_id' => 1,
            'new_agent_id' => 100,
            'notes' => 'Test mapping',
            'created_at' => now(),
        ]);

        $this->assertTrue($inserted);

        $row = \DB::table('_migration_map')
            ->where('old_table', 'stores')
            ->where('old_id', 1)
            ->first();

        $this->assertNotNull($row);
        $this->assertEquals(100, $row->new_agent_id);
        $this->assertEquals('Test mapping', $row->notes);
    }

    public function test_migration_map_supports_both_source_tables(): void
    {
        \DB::table('_migration_map')->insert([
            ['old_table' => 'stores', 'old_id' => 1, 'new_agent_id' => 101, 'created_at' => now()],
            ['old_table' => 'bank_agents', 'old_id' => 5, 'new_agent_id' => 101, 'created_at' => now()],
            ['old_table' => 'bank_agents', 'old_id' => 10, 'new_agent_id' => 102, 'created_at' => now()],
        ]);

        $storeMappings = \DB::table('_migration_map')->where('old_table', 'stores')->count();
        $baMappings = \DB::table('_migration_map')->where('old_table', 'bank_agents')->count();

        $this->assertEquals(1, $storeMappings);
        $this->assertEquals(2, $baMappings);
    }

    public function test_migration_map_idempotency(): void
    {
        \DB::table('_migration_map')->insert([
            'old_table' => 'stores',
            'old_id' => 42,
            'new_agent_id' => 200,
            'created_at' => now(),
        ]);

        $exists = \DB::table('_migration_map')
            ->where('old_table', 'stores')
            ->where('old_id', 42)
            ->exists();

        $this->assertTrue($exists);

        $count = \DB::table('_migration_map')->count();
        $this->assertEquals(1, $count);
    }

    public function test_backup_script_exists_and_is_executable(): void
    {
        $scriptPath = base_path('specs/008-simplify-agent-operations/scripts/backup.sh');

        $this->assertFileExists($scriptPath, 'El script de backup debe existir');
        $this->assertStringContainsString('mysqldump', file_get_contents($scriptPath), 'El script debe contener comandos de backup');
    }

    public function test_rollback_script_exists_and_is_executable(): void
    {
        $scriptPath = base_path('specs/008-simplify-agent-operations/scripts/rollback.sh');

        $this->assertFileExists($scriptPath, 'El script de rollback debe existir');
        $this->assertStringContainsString('migrate:rollback', file_get_contents($scriptPath), 'El script debe contener comandos de rollback');
    }

    public function test_mapping_rules_document_exists(): void
    {
        $rulesPath = base_path('specs/008-simplify-agent-operations/scripts/mapping-rules.md');

        $this->assertFileExists($rulesPath, 'El documento de reglas de mapeo debe existir');

        $content = file_get_contents($rulesPath);

        $this->assertStringContainsString('One Store = One Agent', $content);
        $this->assertStringContainsString('consolidation', $content);
    }

    public function test_fixtures_create_valid_data(): void
    {
        $fix = new MigrationFixtures();
        $fix->create();

        $this->assertNotNull($fix->org);
        $this->assertNotNull($fix->admin);
        $this->assertNotNull($fix->operator);
        $this->assertNotNull($fix->store);
        $this->assertNotNull($fix->bank);
        $this->assertNotNull($fix->bankAgent);
        $this->assertNotNull($fix->type);
        $this->assertCount(5, $fix->operations);
    }

    public function test_baseline_counts_from_fixtures(): void
    {
        $fix = new MigrationFixtures();
        $fix->create();

        $counts = $fix->baselineCounts();

        $this->assertEquals(1, $counts['stores']);
        $this->assertEquals(1, $counts['banks']);
        $this->assertEquals(1, $counts['bank_agents']);
        $this->assertEquals(1, $counts['user_bank_agent_assignments']);
        $this->assertEquals(1, $counts['operation_types']);
        $this->assertEquals(5, $counts['operations']);
    }

    public function test_migration_map_rollback_drops_table(): void
    {
        $this->assertTrue(Schema::hasTable('_migration_map'));

        \DB::table('_migration_map')->insert([
            'old_table' => 'stores',
            'old_id' => 1,
            'new_agent_id' => 999,
            'created_at' => now(),
        ]);

        $this->assertEquals(1, \DB::table('_migration_map')->count());
    }
}
