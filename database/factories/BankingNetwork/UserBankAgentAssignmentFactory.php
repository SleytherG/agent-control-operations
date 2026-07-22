<?php

namespace Database\Factories\BankingNetwork;

use App\Modules\BankingNetwork\Models\UserBankAgentAssignment;
use App\Modules\BankingNetwork\Models\BankAgent;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserBankAgentAssignmentFactory extends Factory
{
    protected $model = UserBankAgentAssignment::class;

    public function definition(): array
    {
        $assignedBy = User::factory()->create();

        return [
            'user_id' => User::factory(),
            'bank_agent_id' => BankAgent::factory(),
            'assigned_by' => $assignedBy->id,
            'assigned_at' => now(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'unassigned_at' => now(),
        ]);
    }
}
