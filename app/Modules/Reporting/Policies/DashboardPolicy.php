<?php

namespace App\Modules\Reporting\Policies;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Models\User;

class DashboardPolicy
{
    public function viewOperatorDashboard(User $actor): bool
    {
        return $actor->role === Role::OPERADOR;
    }

    public function viewAdminDashboard(User $actor): bool
    {
        return $actor->role === Role::ADMINISTRADOR_PROPIETARIO;
    }
}
