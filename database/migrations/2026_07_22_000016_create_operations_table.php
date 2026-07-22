<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignId('store_id')->constrained('stores')->restrictOnDelete();
            $table->foreignId('bank_agent_id')->constrained('bank_agents')->restrictOnDelete();
            $table->foreignId('operation_type_id')->constrained('operation_types')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->decimal('amount', 18, 2);
            $table->char('currency', 3)->default('PEN');
            $table->dateTime('effective_at', 6);
            $table->dateTime('recorded_at', 6);
            $table->string('status', 20)->default('ACTIVE');
            $table->string('reference', 100)->nullable();
            $table->string('observation', 500)->nullable();
            $table->foreignId('annulled_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->dateTime('annulled_at', 6)->nullable();
            $table->string('annulment_reason', 500)->nullable();
            $table->char('idempotency_key', 64)->unique();
            $table->dateTime('created_at', 6);
            $table->dateTime('updated_at', 6);

            $table->index(['user_id', 'effective_at']);
            $table->index(['bank_agent_id', 'effective_at']);
            $table->index(['store_id', 'effective_at']);
            $table->index(['operation_type_id', 'effective_at']);
            $table->index(['status', 'effective_at']);
            $table->index(['organization_id', 'effective_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operations');
    }
};
