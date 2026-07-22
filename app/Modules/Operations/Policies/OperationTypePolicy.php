<?php

namespace App\Modules\Operations\Policies;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Operations\Models\OperationType;

class OperationTypePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->role === Role::ADMINISTRADOR_PROPIETARIO;
    }

    public function view(User $actor, OperationType $type): bool
    {
        return $actor->role === Role::ADMINISTRADOR_PROPIETARIO
            && $actor->organization_id === $type->organization_id;
    }

    public function create(User $actor): bool
    {
        return $actor->role === Role::ADMINISTRADOR_PROPIETARIO;
    }

    public function update(User $actor, OperationType $type): bool
    {
        return $actor->role === Role::ADMINISTRADOR_PROPIETARIO
            && $actor->organization_id === $type->organization_id;
    }

    public function delete(User $actor, OperationType $type): bool
    {
        return $actor->role === Role::ADMINISTRADOR_PROPIETARIO
            && $actor->organization_id === $type->organization_id;
    }
}
