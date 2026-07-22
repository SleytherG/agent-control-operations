<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auth_session_id')->nullable()->constrained('auth_sessions')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('type', 40);
            $table->dateTime('occurred_at', 6);
            $table->json('context')->nullable();
            $table->dateTime('created_at', 6);

            $table->index(['auth_session_id', 'occurred_at']);
            $table->index(['user_id', 'occurred_at']);
            $table->index(['type', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_events');
    }
};
