<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            UPDATE daily_closures SET agent_id = (
                SELECT new_agent_id FROM _migration_map
                WHERE old_table = \'bank_agents\' AND old_id = daily_closures.bank_agent_id
                LIMIT 1
            )
            WHERE agent_id IS NULL AND bank_agent_id IS NOT NULL
        ');

        DB::table('daily_closures')->whereNull('opening_cash')->update([
            'opening_cash' => 0,
        ]);

        DB::table('daily_closures')->whereNull('opening_digital')->update([
            'opening_digital' => 0,
        ]);

        DB::table('daily_closures')->whereNull('expected_closing_cash')->update([
            'expected_closing_cash' => 0,
        ]);

        DB::table('daily_closures')->whereNull('expected_closing_digital')->update([
            'expected_closing_digital' => 0,
        ]);
    }

    public function down(): void
    {
        DB::table('daily_closures')->update([
            'agent_id' => null,
        ]);
    }
};
