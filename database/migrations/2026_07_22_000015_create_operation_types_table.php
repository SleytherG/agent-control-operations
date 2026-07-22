<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operation_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignId('bank_id')->nullable()->constrained('banks')->restrictOnDelete();
            $table->string('name', 160);
            $table->string('description', 500)->nullable();
            $table->string('cash_direction', 20);
            $table->boolean('is_active')->default(true);
            $table->dateTime('deactivated_at', 6)->nullable();
            $table->dateTime('created_at', 6);
            $table->dateTime('updated_at', 6);

            $table->unique(['bank_id', 'name']);
            $table->index(['organization_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operation_types');
    }
};
