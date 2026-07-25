<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operation_types', function (Blueprint $table) {
            $table->tinyInteger('cash_multiplier')->default(0)->after('description');
            $table->tinyInteger('digital_multiplier')->default(0)->after('cash_multiplier');
            $table->unsignedInteger('sort_order')->default(0)->after('digital_multiplier');
        });

        DB::table('operation_types')->where('cash_direction', 'ENTRADA')->update(['cash_multiplier' => 1]);

        DB::table('operation_types')->where('cash_direction', 'SALIDA')->update(['cash_multiplier' => -1]);

        DB::table('operation_types')->whereIn('cash_direction', ['NEUTRA', 'POR_CONFIRMAR'])->update(['cash_multiplier' => 0]);

        DB::table('operation_types')->update(['digital_multiplier' => 0]);

        Schema::table('operation_types', function (Blueprint $table) {
            $table->dropForeign(['bank_id']);
            $table->dropUnique(['bank_id', 'name']);
            $table->dropColumn(['bank_id', 'cash_direction']);
        });
    }

    public function down(): void
    {
        Schema::table('operation_types', function (Blueprint $table) {
            $table->foreignId('bank_id')->nullable()->after('organization_id')->constrained('banks')->restrictOnDelete();
            $table->string('cash_direction', 20)->default('NEUTRA')->after('description');
            $table->dropColumn(['cash_multiplier', 'digital_multiplier', 'sort_order']);
        });
    }
};
