<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth_sessions', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 36)->unique();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 20)->default('ACTIVE');
            $table->dateTime('started_at', 6);
            $table->dateTime('access_expires_at', 6);
            $table->dateTime('absolute_expires_at', 6);
            $table->dateTime('last_refreshed_at', 6)->nullable();
            $table->dateTime('ended_at', 6)->nullable();
            $table->string('end_reason', 40)->nullable();
            $table->binary('ip_hash', 32)->nullable();
            $table->string('user_agent_summary', 255)->nullable();
            $table->dateTime('created_at', 6);
            $table->dateTime('updated_at', 6);

            $table->index(['user_id', 'started_at']);
            $table->index(['user_id', 'status', 'started_at']);
            $table->index(['status', 'access_expires_at']);
            $table->index(['absolute_expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_sessions');
    }
};
