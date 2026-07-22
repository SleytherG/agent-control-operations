<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_closure_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_closure_id')->constrained('daily_closures')->restrictOnDelete();
            $table->foreignId('operation_id')->unique()->constrained('operations')->restrictOnDelete();
            $table->dateTime('created_at', 6);

            $table->index(['daily_closure_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_closure_operations');
    }
};
