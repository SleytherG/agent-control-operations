<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auth_sessions', function (Blueprint $table) {
            $table->foreignId('password_reset_id')
                ->nullable()
                ->unique()
                ->after('user_id')
                ->constrained('password_resets')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('auth_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('password_reset_id');
        });
    }
};
