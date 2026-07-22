<?php

namespace Database\Seeders;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::create([
            'public_id' => (string) Str::uuid(),
            'name' => 'Red Principal',
            'timezone' => 'America/Lima',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        User::create([
            'public_id' => (string) Str::uuid(),
            'organization_id' => $org->id,
            'username_normalized' => Str::lower('admin'),
            'email_normalized' => Str::lower('admin@controloperaciones.local'),
            'password' => Hash::make('password'),
            'role' => Role::ADMINISTRADOR_PROPIETARIO,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->call(OperationalStructureSeeder::class);
    }
}
