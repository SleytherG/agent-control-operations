<?php

namespace App\Modules\BankingNetwork\Policies;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\BankingNetwork\Models\BankAgent;

class BankAgentPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->role === Role::ADMINISTRADOR_PROPIETARIO;
    }

    public function view(User $actor, BankAgent $agent): bool
    {
        return $actor->role === Role::ADMINISTRADOR_PROPIETARIO
            && $actor->organization_id === $agent->organization_id;
    }

    public function create(User $actor): bool
    {
        return $actor->role === Role::ADMINISTRADOR_PROPIETARIO;
    }

    public function update(User $actor, BankAgent $agent): bool
    {
        return $actor->role === Role::ADMINISTRADOR_PROPIETARIO
            && $actor->organization_id === $agent->organization_id;
    }

    public function deactivate(User $actor, BankAgent $agent): bool
    {
        return $actor->role === Role::ADMINISTRADOR_PROPIETARIO
            && $actor->organization_id === $agent->organization_id;
    }
}
