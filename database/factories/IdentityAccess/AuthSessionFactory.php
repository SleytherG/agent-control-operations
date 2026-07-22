<?php

namespace Database\Factories\IdentityAccess;

use App\Modules\IdentityAccess\Domain\Enums\AuthSessionStatus;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AuthSessionFactory extends Factory
{
    protected $model = AuthSession::class;

    public function definition(): array
    {
        $startedAt = now();
        $accessExpiresAt = $startedAt->copy()->addSeconds(300);
        $absoluteExpiresAt = $startedAt->copy()->addHours(8);

        return [
            'public_id' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'status' => AuthSessionStatus::ACTIVE,
            'started_at' => $startedAt,
            'access_expires_at' => $accessExpiresAt,
            'absolute_expires_at' => $absoluteExpiresAt,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AuthSessionStatus::EXPIRED,
            'ended_at' => now(),
            'end_reason' => 'EXPIRACION',
        ]);
    }

    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AuthSessionStatus::REVOKED,
            'ended_at' => now(),
            'end_reason' => 'LOGOUT_MANUAL',
        ]);
    }
}
