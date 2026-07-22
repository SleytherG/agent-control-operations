<?php

namespace App\Modules\IdentityAccess\Application\Actions;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListAuthorizedSessions
{
    public function execute(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = AuthSession::with(['user', 'events']);

        if ($user->role === Role::OPERADOR) {
            $query->where('user_id', $user->id);
        } elseif ($user->role === Role::ADMINISTRADOR_PROPIETARIO) {
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['user_id']) && $user->role === Role::ADMINISTRADOR_PROPIETARIO) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['from'])) {
            $query->where('started_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->where('started_at', '<=', $filters['to']);
        }

        $perPage = $filters['per_page'] ?? config('session-security.history.default_page_size', 25);

        return $query->orderBy('started_at', 'desc')->paginate($perPage);
    }
}
