<?php

namespace App\Modules\IdentityAccess\Policies;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;

class UserPolicy
{
    public function deactivate(User $actor, User $target): bool
    {
        if ($actor->role !== Role::ADMINISTRADOR_PROPIETARIO) {
            return false;
        }

        if ($target->id === $actor->id) {
            return false;
        }

        return $actor->organization_id === $target->organization_id;
    }

    public function viewAny(User $actor): bool
    {
        return $actor->role === Role::ADMINISTRADOR_PROPIETARIO;
    }

    public function createOperator(User $actor): bool
    {
        return $actor->role === Role::ADMINISTRADOR_PROPIETARIO;
    }

    public function updateOperator(User $actor, User $target): bool
    {
        return $actor->role === Role::ADMINISTRADOR_PROPIETARIO
            && $actor->organization_id === $target->organization_id
            && $target->role === Role::OPERADOR;
    }

    public function deactivateOperator(User $actor, User $target): bool
    {
        if ($actor->role !== Role::ADMINISTRADOR_PROPIETARIO) {
            return false;
        }

        if ($target->id === $actor->id) {
            return false;
        }

        return $actor->organization_id === $target->organization_id
            && $target->role === Role::OPERADOR;
    }

    public function resetPassword(User $actor, User $target): bool
    {
        return $actor->role === Role::ADMINISTRADOR_PROPIETARIO
            && $actor->organization_id === $target->organization_id
            && $target->role === Role::OPERADOR
            && $target->status === UserStatus::ACTIVE;
    }

    public function viewPasswordResetAudit(User $actor, User $target): bool
    {
        return $actor->role === Role::ADMINISTRADOR_PROPIETARIO
            && $actor->organization_id === $target->organization_id
            && $target->role === Role::OPERADOR;
    }
}
