<?php

namespace Database\Factories\Operations;

use App\Modules\Operations\Models\Operation;
use App\Modules\Operations\Models\OperationType;
use App\Modules\BankingNetwork\Models\BankAgent;
use App\Modules\Organization\Models\Store;
use App\Modules\Organization\Models\Organization;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OperationFactory extends Factory
{
    protected $model = Operation::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'store_id' => Store::factory(),
            'bank_agent_id' => BankAgent::factory(),
            'operation_type_id' => OperationType::factory(),
            'user_id' => User::factory(),
            'amount' => fake()->randomFloat(2, 0.01, 999999.99),
            'currency' => 'PEN',
            'effective_at' => now(),
            'recorded_at' => now(),
            'status' => Operation::STATUS_ACTIVE,
            'reference' => fake()->optional()->bothify('REF-####'),
            'observation' => fake()->optional()->sentence(),
            'idempotency_key' => Str::random(64),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function annulled(?int $annulledBy = null, ?string $reason = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Operation::STATUS_ANNULLED,
            'annulled_by' => $annulledBy ?? User::factory(),
            'annulled_at' => now(),
            'annulment_reason' => $reason ?? fake()->sentence(),
        ]);
    }

    public function withIdempotencyKey(string $key): static
    {
        return $this->state(fn (array $attributes) => [
            'idempotency_key' => $key,
        ]);
    }

    public function withAmount(string $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
        ]);
    }

    public function atDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'effective_at' => $date,
            'recorded_at' => $date,
        ]);
    }
}
