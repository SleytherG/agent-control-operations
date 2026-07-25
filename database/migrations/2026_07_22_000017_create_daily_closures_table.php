<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_closures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignId('store_id')->nullable()->constrained('stores')->restrictOnDelete();
            $table->foreignId('bank_agent_id')->nullable()->constrained('bank_agents')->restrictOnDelete();
            $table->date('business_date');
            $table->string('status', 20)->default('ACTIVO');
            $table->integer('operation_count')->unsigned()->default(0);
            $table->decimal('gross_amount', 18, 2)->default(0);
            $table->decimal('cash_in', 18, 2)->default(0);
            $table->decimal('cash_out', 18, 2)->default(0);
            $table->decimal('net_movement', 18, 2)->default(0);
            $table->boolean('has_pending_confirm')->default(false);
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->dateTime('confirmed_at', 6)->nullable();
            $table->foreignId('reopened_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->dateTime('reopened_at', 6)->nullable();
            $table->string('reopen_reason', 500)->nullable();
            $table->dateTime('created_at', 6);
            $table->dateTime('updated_at', 6);

            $table->unique(['bank_agent_id', 'business_date', 'status']);
            $table->index(['bank_agent_id', 'business_date']);
            $table->index(['organization_id', 'business_date']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_closures');
    }
};
