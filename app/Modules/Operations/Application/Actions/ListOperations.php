<?php

namespace App\Modules\Operations\Application\Actions;

use App\Modules\Operations\Models\Operation;
use Illuminate\Pagination\LengthAwarePaginator;

class ListOperations
{
    public function execute(array $filters, bool $isAdmin, int $userId, int $organizationId): LengthAwarePaginator
    {
        $query = Operation::with(['bankAgent.store', 'bankAgent.bank', 'operationType', 'user'])
            ->where('organization_id', $organizationId);

        if (! $isAdmin) {
            $query->where('user_id', $userId);
        }

        if (! empty($filters['bank_agent_id'])) {
            $query->where('bank_agent_id', $filters['bank_agent_id']);
        }

        if (! empty($filters['operation_type_id'])) {
            $query->where('operation_type_id', $filters['operation_type_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['user_id']) && $isAdmin) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('effective_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('effective_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('effective_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(25)
            ->withQueryString();
    }
}
