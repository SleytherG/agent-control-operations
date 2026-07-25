<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_resets', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 36)->unique();
            $table->foreignId('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('initiated_by_user_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 20);
            $table->dateTime('issued_at', 6);
            $table->dateTime('expires_at', 6);
            $table->dateTime('consumed_at', 6)->nullable();
            $table->dateTime('completed_at', 6)->nullable();
            $table->dateTime('superseded_at', 6)->nullable();
            $table->string('reason', 500)->nullable();
            $table->dateTime('created_at', 6);
            $table->dateTime('updated_at', 6);

            $table->index(['user_id', 'status', 'issued_at']);
            $table->index(['organization_id', 'status', 'issued_at']);
            $table->index('expires_at');
            $table->index(['initiated_by_user_id', 'issued_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_resets');
    }
};
