<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE auth_sessions ALTER COLUMN ip_hash TYPE char(64) USING encode(ip_hash, 'hex')");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE auth_sessions ALTER COLUMN ip_hash TYPE bytea USING decode(ip_hash, 'hex')");
        }
    }
};
