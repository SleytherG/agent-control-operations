<?php

namespace Database\Factories\IdentityAccess;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::uuid(),
            'organization_id' => Organization::factory(),
            'username_normalized' => Str::lower(fake()->unique()->userName()),
            'email_normalized' => Str::lower(fake()->unique()->safeEmail()),
            'password' => Hash::make('password'),
            'password_changed_at' => now(),
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function administradorPropietario(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::ADMINISTRADOR_PROPIETARIO,
            'password_changed_at' => now(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::INACTIVE,
            'deactivated_at' => now(),
            'deactivated_by' => null,
            'deactivation_reason' => 'Test deactivation',
        ]);
    }
}
