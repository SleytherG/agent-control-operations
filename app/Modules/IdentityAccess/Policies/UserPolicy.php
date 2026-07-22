<?php

namespace App\Modules\IdentityAccess\Policies;

use App\Modules\IdentityAccess\Domain\Enums\Role;
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
}
