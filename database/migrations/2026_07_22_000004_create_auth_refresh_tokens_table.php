<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth_refresh_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auth_session_id')->constrained('auth_sessions')->restrictOnDelete();
            $table->binary('token_hash', 32);
            $table->unsignedInteger('generation')->default(1);
            $table->string('state', 20)->default('ACTIVE');
            $table->dateTime('issued_at', 6);
            $table->dateTime('expires_at', 6);
            $table->dateTime('consumed_at', 6)->nullable();
            $table->dateTime('revoked_at', 6)->nullable();
            $table->foreignId('replaced_by_id')->nullable()->constrained('auth_refresh_tokens')->restrictOnDelete();
            $table->dateTime('created_at', 6);
            $table->dateTime('updated_at', 6);

            $table->unique('token_hash');
            $table->unique(['auth_session_id', 'generation']);
            $table->index(['auth_session_id', 'state']);
            $table->index(['expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_refresh_tokens');
    }
};
