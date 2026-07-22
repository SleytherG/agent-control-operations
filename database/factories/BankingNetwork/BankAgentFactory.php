<?php

namespace Database\Factories\BankingNetwork;

use App\Modules\BankingNetwork\Models\BankAgent;
use App\Modules\BankingNetwork\Models\Bank;
use App\Modules\Organization\Models\Store;
use App\Modules\Organization\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankAgentFactory extends Factory
{
    protected $model = BankAgent::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'store_id' => Store::factory(),
            'bank_id' => Bank::factory(),
            'code' => fake()->unique()->bothify('AG-####'),
            'terminal_code' => fake()->optional()->bothify('TERM-###'),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'deactivated_at' => now(),
        ]);
    }
}
