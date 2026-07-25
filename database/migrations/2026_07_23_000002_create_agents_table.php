<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->string('code', 80);
            $table->string('name', 200);
            $table->string('city', 160);
            $table->string('region', 160)->nullable();
            $table->string('province', 160)->nullable();
            $table->string('district', 160)->nullable();
            $table->string('address', 500)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('deactivated_at', 6)->nullable();
            $table->dateTime('created_at', 6);
            $table->dateTime('updated_at', 6);

            $table->unique(['organization_id', 'code']);
            $table->index(['organization_id', 'is_active']);
            $table->index(['city', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
