<?php

namespace Database\Factories\IdentityAccess;

use App\Modules\IdentityAccess\Domain\Enums\PasswordResetStatus;
use App\Modules\IdentityAccess\Models\PasswordReset;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PasswordResetFactory extends Factory
{
    protected $model = PasswordReset::class;

    public function definition(): array
    {
        $issuedAt = now();

        return [
            'public_id' => (string) Str::uuid(),
            'organization_id' => fn (array $attributes) => User::find($attributes['user_id'])?->organization_id,
            'user_id' => User::factory(),
            'initiated_by_user_id' => User::factory()->administradorPropietario(),
            'status' => PasswordResetStatus::PENDING,
            'issued_at' => $issuedAt,
            'expires_at' => $issuedAt->copy()->addSeconds(3600),
            'created_at' => $issuedAt,
            'updated_at' => $issuedAt,
        ];
    }
}
