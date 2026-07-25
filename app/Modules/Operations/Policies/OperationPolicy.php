<?php

namespace App\Modules\Operations\Policies;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Operations\Models\Operation;

class OperationPolicy
{
    public function viewAny(User $actor): bool
    {
        return in_array($actor->role, [Role::ADMINISTRADOR_PROPIETARIO, Role::OPERADOR]);
    }

    public function view(User $actor, Operation $operation): bool
    {
        if ($actor->role === Role::ADMINISTRADOR_PROPIETARIO) {
            return $actor->organization_id === $operation->organization_id;
        }

        return $operation->user_id === $actor->id
            && $actor->organization_id === $operation->organization_id;
    }

    public function register(User $actor): bool
    {
        return in_array($actor->role, [Role::ADMINISTRADOR_PROPIETARIO, Role::OPERADOR]);
    }

    public function annul(User $actor, Operation $operation): bool
    {
        if ($actor->role === Role::ADMINISTRADOR_PROPIETARIO) {
            return $actor->organization_id === $operation->organization_id
                && $operation->isActive();
        }

        $hoursSinceRecorded = abs(now()->diffInHours($operation->recorded_at));
        $withinWindow = $hoursSinceRecorded <= config('operations.annulment_window_hours', 24);

        return $operation->user_id === $actor->id
            && $operation->isActive()
            && $withinWindow;
    }
}
