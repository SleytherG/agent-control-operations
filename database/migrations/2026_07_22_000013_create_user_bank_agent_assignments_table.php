<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_bank_agent_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('bank_agent_id')->constrained('bank_agents')->restrictOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('assigned_at', 6);
            $table->dateTime('unassigned_at', 6)->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at', 6);
            $table->dateTime('updated_at', 6);

            $table->index(['user_id', 'is_active']);
            $table->index(['bank_agent_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_bank_agent_assignments');
    }
};
