<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->string('internal_code', 30)->nullable()->after('id');
            $table->foreignId('agent_id')->nullable()->after('organization_id')->constrained('agents')->restrictOnDelete();
            $table->string('customer_name', 200)->nullable()->after('operation_type_id');
            $table->decimal('cash_delta', 18, 2)->default(0)->after('amount');
            $table->decimal('digital_delta', 18, 2)->default(0)->after('cash_delta');
        });
    }

    public function down(): void
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->dropColumn(['internal_code', 'agent_id', 'customer_name', 'cash_delta', 'digital_delta']);
        });
    }
};
