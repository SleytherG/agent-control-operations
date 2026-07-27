<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE auth_refresh_tokens ALTER COLUMN token_hash TYPE char(64) USING encode(token_hash, 'hex')");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE auth_refresh_tokens ALTER COLUMN token_hash TYPE bytea USING decode(token_hash, 'hex')");
        }
    }
};
