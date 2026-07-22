<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 36)->unique();
            $table->string('name', 160);
            $table->string('timezone', 64)->default('America/Lima');
            $table->boolean('is_active')->default(true);
            $table->dateTime('deactivated_at', 6)->nullable();
            $table->dateTime('created_at', 6);
            $table->dateTime('updated_at', 6);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
