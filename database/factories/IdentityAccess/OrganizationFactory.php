<?php

namespace Database\Factories\IdentityAccess;

use App\Modules\Organization\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::uuid(),
            'name' => fake()->company(),
            'timezone' => 'America/Lima',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
