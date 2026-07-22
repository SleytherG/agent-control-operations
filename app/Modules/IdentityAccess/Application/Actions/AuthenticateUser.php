<?php

namespace App\Modules\IdentityAccess\Application\Actions;

use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthenticateUser
{
    public function execute(string $identifier, string $password): ?User
    {
        $normalized = mb_strtolower(trim($identifier));

        $user = User::where('username_normalized', $normalized)
            ->orWhere('email_normalized', $normalized)
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        if ($user->status !== UserStatus::ACTIVE) {
            return null;
        }

        return $user;
    }
}
