<?php

namespace App\Modules\Organization\Policies;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Province;

class ProvincePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->role === Role::ADMINISTRADOR_PROPIETARIO;
    }

    public function view(User $actor, Province $province): bool
    {
        return $actor->role === Role::ADMINISTRADOR_PROPIETARIO
            && $actor->organization_id === $province->organization_id;
    }

    public function create(User $actor): bool
    {
        return $actor->role === Role::ADMINISTRADOR_PROPIETARIO;
    }

    public function update(User $actor, Province $province): bool
    {
        return $actor->role === Role::ADMINISTRADOR_PROPIETARIO
            && $actor->organization_id === $province->organization_id;
    }

    public function deactivate(User $actor, Province $province): bool
    {
        return $actor->role === Role::ADMINISTRADOR_PROPIETARIO
            && $actor->organization_id === $province->organization_id;
    }
}
