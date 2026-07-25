<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('_migration_map', function (Blueprint $table) {
            $table->id();
            $table->string('old_table', 50);
            $table->unsignedBigInteger('old_id');
            $table->unsignedBigInteger('new_agent_id');
            $table->string('notes', 255)->nullable();
            $table->dateTime('created_at', 6);

            $table->index(['old_table', 'old_id']);
            $table->index(['new_agent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('_migration_map');
    }
};
