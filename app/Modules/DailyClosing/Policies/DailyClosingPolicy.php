<?php

namespace App\Modules\DailyClosing\Policies;

use App\Modules\DailyClosing\Models\DailyClosure;
use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\BankingNetwork\Models\UserBankAgentAssignment;

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

        return UserBankAgentAssignment::where('user_id', $actor->id)
            ->where('bank_agent_id', $closure->bank_agent_id)
            ->where('is_active', true)
            ->exists();
    }

    public function generate(User $actor, int $bankAgentId): bool
    {
        if ($actor->role === Role::ADMINISTRADOR_PROPIETARIO) {
            return true;
        }

        return UserBankAgentAssignment::where('user_id', $actor->id)
            ->where('bank_agent_id', $bankAgentId)
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
