<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 36)->unique();
            $table->foreignId('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->string('username_normalized', 100);
            $table->string('email_normalized', 254);
            $table->string('password');
            $table->string('role', 40);
            $table->string('status', 20)->default('ACTIVE');
            $table->dateTime('deactivated_at', 6)->nullable();
            $table->foreignId('deactivated_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('deactivation_reason', 500)->nullable();
            $table->dateTime('created_at', 6);
            $table->dateTime('updated_at', 6);

            $table->unique(['organization_id', 'username_normalized']);
            $table->unique(['organization_id', 'email_normalized']);
            $table->index(['organization_id', 'role', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
