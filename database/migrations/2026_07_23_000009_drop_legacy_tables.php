<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (in_array(DB::getDriverName(), ['sqlite', 'pgsql'])) {
            if (DB::getDriverName() === 'sqlite') {
                return;
            }
            if (DB::getDriverName() === 'pgsql') {
                $this->dropOrphanIndexes();
                $this->dropLegacyForeignKeys();
                $this->dropLegacyTables();
                $this->dropLegacyColumns();
                return;
            }
        }
        $this->dropOrphanIndexes();
        $this->dropLegacyForeignKeys();

        $this->dropLegacyTables();

        $this->dropLegacyColumns();
    }

    private function dropOrphanIndexes(): void
    {
        DB::statement('DROP INDEX IF EXISTS operations_bank_agent_id_effective_at_index');
        DB::statement('DROP INDEX IF EXISTS operations_store_id_effective_at_index');
        DB::statement('ALTER TABLE daily_closures DROP CONSTRAINT IF EXISTS daily_closures_bank_agent_id_business_date_status_unique');
        DB::statement('DROP INDEX IF EXISTS daily_closures_bank_agent_id_business_date_index');
    }

    private function dropLegacyForeignKeys(): void
    {
        $fks = [
            'operations_store_id_foreign',
            'operations_bank_agent_id_foreign',
            'daily_closures_store_id_foreign',
            'daily_closures_bank_agent_id_foreign',
            'operation_types_bank_id_foreign',
            'bank_agents_store_id_foreign',
            'bank_agents_bank_id_foreign',
        ];

        foreach ($fks as $fk) {
            foreach (['operations', 'daily_closures', 'operation_types', 'bank_agents'] as $table) {
                if (Schema::hasTable($table)) {
                    DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$fk}");
                }
            }
        }
    }

    private function dropLegacyTables(): void
    {
        Schema::dropIfExists('user_bank_agent_assignments');
        Schema::dropIfExists('bank_agents');
        Schema::dropIfExists('stores');
        Schema::dropIfExists('banks');
        Schema::dropIfExists('_migration_map');
    }

    private function dropLegacyColumns(): void
    {
        $this->safeDropColumns('operations', ['store_id', 'bank_agent_id', 'reference']);
        $this->safeDropColumns('daily_closures', ['store_id', 'bank_agent_id', 'cash_in', 'cash_out', 'net_movement', 'has_pending_confirm']);
        $this->safeDropColumns('operation_types', ['cash_direction']);

        DB::statement('UPDATE operations SET agent_id = 1 WHERE agent_id IS NULL');
        DB::statement('UPDATE daily_closures SET agent_id = 1 WHERE agent_id IS NULL');
    }

    private function safeDropColumns(string $table, array $columns): void
    {
        $existing = array_filter($columns, fn ($col) => Schema::hasColumn($table, $col));

        if (empty($existing)) {
            return;
        }

        Schema::table($table, function ($blueprint) use ($existing) {
            $blueprint->dropColumn(array_values($existing));
        });
    }

    public function down(): void
    {
        // Full restore via backup/rollback.sh + migrate:rollback per phase
    }
};
