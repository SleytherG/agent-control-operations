<?php

namespace App\Modules\IdentityAccess\Application\Actions;

use App\Modules\Audit\Models\AuditLog;
use App\Modules\IdentityAccess\Domain\Enums\PasswordResetStatus;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListPasswordResetAudit
{
    public function execute(User $actor, User $target, array $filters): LengthAwarePaginator
    {
        $query = AuditLog::query()
            ->with('actor')
            ->where('organization_id', $actor->organization_id)
            ->where('entity_type', User::class)
            ->where('entity_id', $target->id)
            ->where('action', 'like', 'password_reset.%');

        if (! empty($filters['status'])) {
            $status = PasswordResetStatus::from($filters['status']);
            $action = match ($status) {
                PasswordResetStatus::PENDING => 'password_reset.issued',
                PasswordResetStatus::CONSUMED => 'password_reset.consumed',
                PasswordResetStatus::COMPLETED => 'password_reset.completed',
                PasswordResetStatus::SUPERSEDED => 'password_reset.superseded',
                PasswordResetStatus::EXPIRED => 'password_reset.expired',
            };
            $query->where('action', $action);
        }
        if (! empty($filters['from'])) {
            $query->where('occurred_at', '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $query->where('occurred_at', '<=', $filters['to'].' 23:59:59.999999');
        }

        return $query->latest('occurred_at')->paginate(25)->withQueryString();
    }
}
