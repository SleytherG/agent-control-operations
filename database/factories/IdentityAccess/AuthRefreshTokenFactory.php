<?php

namespace Database\Factories\IdentityAccess;

use App\Modules\IdentityAccess\Domain\Enums\RefreshTokenState;
use App\Modules\IdentityAccess\Models\AuthRefreshToken;
use App\Modules\IdentityAccess\Models\AuthSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuthRefreshTokenFactory extends Factory
{
    protected $model = AuthRefreshToken::class;

    public function definition(): array
    {
        $now = now();
        return [
            'auth_session_id' => AuthSession::factory(),
            'token_hash' => hash('sha256', fake()->sha256(), true),
            'generation' => 1,
            'state' => RefreshTokenState::ACTIVE,
            'issued_at' => $now,
            'expires_at' => $now->copy()->addSeconds(300),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    public function consumed(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => RefreshTokenState::CONSUMED,
            'consumed_at' => now(),
        ]);
    }
}
