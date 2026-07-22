<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('action', 100);
            $table->string('entity_type', 100);
            $table->unsignedBigInteger('entity_id');
            $table->json('before_values')->nullable();
            $table->json('after_values')->nullable();
            $table->string('reason', 500)->nullable();
            $table->dateTime('occurred_at', 6);
            $table->char('correlation_id', 36);
            $table->dateTime('created_at', 6);

            $table->index(['entity_type', 'entity_id', 'occurred_at']);
            $table->index(['actor_user_id', 'occurred_at']);
            $table->index(['organization_id', 'occurred_at']);
            $table->index(['action', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
