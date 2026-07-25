<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_closures', function (Blueprint $table) {
            $table->foreignId('agent_id')->nullable()->after('organization_id')->constrained('agents')->restrictOnDelete();
            $table->decimal('total_cash_in', 18, 2)->default(0)->after('gross_amount');
            $table->decimal('total_cash_out', 18, 2)->default(0)->after('total_cash_in');
            $table->decimal('total_digital_in', 18, 2)->default(0)->after('total_cash_out');
            $table->decimal('total_digital_out', 18, 2)->default(0)->after('total_digital_in');
            $table->decimal('opening_cash', 18, 2)->default(0)->after('total_digital_out');
            $table->decimal('opening_digital', 18, 2)->default(0)->after('opening_cash');
            $table->decimal('expected_closing_cash', 18, 2)->default(0)->after('opening_digital');
            $table->decimal('expected_closing_digital', 18, 2)->default(0)->after('expected_closing_cash');
            $table->decimal('actual_closing_cash', 18, 2)->nullable()->after('expected_closing_digital');
            $table->decimal('actual_closing_digital', 18, 2)->nullable()->after('actual_closing_cash');
            $table->decimal('cash_difference', 18, 2)->nullable()->after('actual_closing_digital');
            $table->decimal('digital_difference', 18, 2)->nullable()->after('cash_difference');
            $table->boolean('has_inconsistencies')->default(false)->after('digital_difference');
            $table->foreignId('opened_by')->nullable()->after('has_inconsistencies')->constrained('users')->restrictOnDelete();
            $table->foreignId('submitted_by')->nullable()->after('opened_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('opened_at', 6)->nullable()->after('submitted_by');
            $table->dateTime('submitted_at', 6)->nullable()->after('opened_at');
            $table->string('notes', 500)->nullable()->after('reopen_reason');
        });
    }

    public function down(): void
    {
        Schema::table('daily_closures', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->dropColumn([
                'agent_id', 'total_cash_in', 'total_cash_out',
                'total_digital_in', 'total_digital_out',
                'opening_cash', 'opening_digital',
                'expected_closing_cash', 'expected_closing_digital',
                'actual_closing_cash', 'actual_closing_digital',
                'cash_difference', 'digital_difference', 'has_inconsistencies',
                'opened_by', 'submitted_by', 'opened_at', 'submitted_at', 'notes',
            ]);
        });
    }
};
