<?php

namespace App\Modules\IdentityAccess\Policies;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\User;

class AuthSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === Role::ADMINISTRADOR_PROPIETARIO
            || $user->role === Role::OPERADOR;
    }

    public function view(User $user, AuthSession $session): bool
    {
        if ($user->role === Role::ADMINISTRADOR_PROPIETARIO) {
            return $session->user->organization_id === $user->organization_id;
        }

        return $session->user_id === $user->id;
    }
}
