<?php

namespace App\Modules\DailyClosing\Policies;

use App\Modules\DailyClosing\Models\DailyClosure;
use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Agents\Models\UserAgentAssignment;

class DailyClosingPolicy
{
    public function viewAny(User $actor): bool
    {
        return in_array($actor->role, [Role::ADMINISTRADOR_PROPIETARIO, Role::OPERADOR]);
    }

    public function view(User $actor, DailyClosure $closure): bool
    {
        if ($actor->organization_id !== $closure->organization_id) {
            return false;
        }

        if ($actor->role === Role::ADMINISTRADOR_PROPIETARIO) {
            return true;
        }

        return UserAgentAssignment::where('user_id', $actor->id)
            ->where('agent_id', $closure->agent_id)
            ->where('is_active', true)
            ->exists();
    }

    public function generate(User $actor, int $agentId): bool
    {
        if ($actor->role === Role::ADMINISTRADOR_PROPIETARIO) {
            return true;
        }

        return UserAgentAssignment::where('user_id', $actor->id)
            ->where('agent_id', $agentId)
            ->where('is_active', true)
            ->exists();
    }

    public function confirm(User $actor, DailyClosure $closure): bool
    {
        return $actor->role === Role::ADMINISTRADOR_PROPIETARIO
            && $actor->organization_id === $closure->organization_id;
    }

    public function reopen(User $actor, DailyClosure $closure): bool
    {
        return $actor->role === Role::ADMINISTRADOR_PROPIETARIO
            && $actor->organization_id === $closure->organization_id;
    }
}
