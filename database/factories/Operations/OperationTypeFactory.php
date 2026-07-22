<?php

namespace Database\Factories\Operations;

use App\Modules\Operations\Models\OperationType;
use App\Modules\Organization\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class OperationTypeFactory extends Factory
{
    protected $model = OperationType::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'bank_id' => null,
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'cash_direction' => fake()->randomElement(['ENTRADA', 'SALIDA', 'NEUTRA', 'POR_CONFIRMAR']),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function forBank(int $bankId): static
    {
        return $this->state(fn (array $attributes) => [
            'bank_id' => $bankId,
        ]);
    }

    public function general(): static
    {
        return $this->state(fn (array $attributes) => [
            'bank_id' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'deactivated_at' => now(),
        ]);
    }
}
