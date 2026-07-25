<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            UPDATE operations SET agent_id = (
                SELECT new_agent_id FROM _migration_map
                WHERE old_table = \'bank_agents\' AND old_id = operations.bank_agent_id
                LIMIT 1
            )
            WHERE agent_id IS NULL AND bank_agent_id IS NOT NULL
        ');

        DB::statement('
            UPDATE operations SET
                cash_delta = (SELECT cash_multiplier FROM operation_types WHERE id = operations.operation_type_id) * amount,
                digital_delta = (SELECT digital_multiplier FROM operation_types WHERE id = operations.operation_type_id) * amount
            WHERE cash_delta = 0 AND digital_delta = 0 AND amount > 0
        ');

        $operations = DB::table('operations')
            ->whereNull('internal_code')
            ->orderBy('id')
            ->get();

        foreach ($operations as $op) {
            DB::table('operations')
                ->where('id', $op->id)
                ->update([
                    'internal_code' => 'OP-LEGACY-' . $op->id,
                ]);
        }

        DB::table('operations')->whereNull('customer_name')->update(['customer_name' => null]);
    }

    public function down(): void
    {
        DB::table('operations')->update([
            'agent_id' => null,
            'internal_code' => null,
            'cash_delta' => 0,
            'digital_delta' => 0,
            'customer_name' => null,
        ]);
    }
};
