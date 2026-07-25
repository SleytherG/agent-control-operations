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
        try {
            Schema::table('operations', function ($table) {
                $table->dropIndex(['bank_agent_id', 'effective_at']);
            });
        } catch (\Throwable $e) {
        }

        try {
            Schema::table('operations', function ($table) {
                $table->dropIndex(['store_id', 'effective_at']);
            });
        } catch (\Throwable $e) {
        }

        try {
            Schema::table('daily_closures', function ($table) {
                $table->dropUnique(['bank_agent_id', 'business_date', 'status']);
            });
        } catch (\Throwable $e) {
        }

        try {
            Schema::table('daily_closures', function ($table) {
                $table->dropIndex(['bank_agent_id', 'business_date']);
            });
        } catch (\Throwable $e) {
        }
    }

    private function dropLegacyForeignKeys(): void
    {
        $fkMap = [
            ['operations', 'store_id', 'stores'],
            ['operations', 'bank_agent_id', 'bank_agents'],
            ['daily_closures', 'store_id', 'stores'],
            ['daily_closures', 'bank_agent_id', 'bank_agents'],
            ['operation_types', 'bank_id', 'banks'],
            ['bank_agents', 'store_id', 'stores'],
            ['bank_agents', 'bank_id', 'banks'],
        ];

        foreach ($fkMap as [$table, $column, $refTable]) {
            try {
                Schema::table($table, function ($blueprint) use ($column, $refTable) {
                    $blueprint->dropForeign([$column]);
                });
            } catch (\Throwable $e) {
                // FK may not exist if already dropped
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
        try {
            Schema::table('operations', function ($table) {
                $table->dropColumn(['store_id', 'bank_agent_id', 'reference']);
            });
        } catch (\Throwable $e) {
            // columns may not exist
        }

        try {
            Schema::table('daily_closures', function ($table) {
                $table->dropColumn(['store_id', 'bank_agent_id', 'cash_in', 'cash_out', 'net_movement', 'has_pending_confirm']);
            });
        } catch (\Throwable $e) {
            // columns may not exist
        }

        try {
            Schema::table('operation_types', function ($table) {
                $table->dropColumn('cash_direction');
            });
        } catch (\Throwable $e) {
            // column may not exist
        }

        DB::statement('UPDATE operations SET agent_id = 1 WHERE agent_id IS NULL');
        DB::statement('UPDATE daily_closures SET agent_id = 1 WHERE agent_id IS NULL');
    }

    public function down(): void
    {
        // Full restore via backup/rollback.sh + migrate:rollback per phase
    }
};
