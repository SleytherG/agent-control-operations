<?php

namespace App\Modules\Reporting\Services;

use App\Modules\IdentityAccess\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardQueryService
{
    public function getOperatorMetrics(User $user, Carbon $startUtc, Carbon $endUtc): object
    {
        return DB::table('operations as o')
            ->where('o.status', 'ACTIVE')
            ->where('o.user_id', $user->id)
            ->whereBetween('o.effective_at', [$startUtc, $endUtc])
            ->selectRaw("
                COUNT(*) as operation_count,
                COALESCE(SUM(o.amount), 0) as gross_amount,
                COALESCE(SUM(CASE WHEN o.cash_delta > 0 THEN o.cash_delta ELSE 0 END), 0) as cash_in,
                COALESCE(SUM(CASE WHEN o.cash_delta < 0 THEN ABS(o.cash_delta) ELSE 0 END), 0) as cash_out,
                COALESCE(SUM(CASE WHEN o.digital_delta > 0 THEN o.digital_delta ELSE 0 END), 0) as digital_in,
                COALESCE(SUM(CASE WHEN o.digital_delta < 0 THEN ABS(o.digital_delta) ELSE 0 END), 0) as digital_out,
                COALESCE(SUM(CASE WHEN o.cash_delta > 0 THEN o.cash_delta ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN o.cash_delta < 0 THEN ABS(o.cash_delta) ELSE 0 END), 0) as net_movement
            ")
            ->first();
    }

    public function getTypeDistribution(?User $user, Carbon $startUtc, Carbon $endUtc, array $filters = []): array
    {
        $query = DB::table('operations as o')
            ->join('operation_types as ot', 'o.operation_type_id', '=', 'ot.id')
            ->where('o.status', 'ACTIVE')
            ->whereBetween('o.effective_at', [$startUtc, $endUtc]);

        if ($user) {
            $query->where('o.user_id', $user->id);
        }

        $this->applyAdminFilters($query, $filters);

        return $query->selectRaw("
            ot.name, COUNT(*) as count, COALESCE(SUM(o.amount), 0) as total_amount
        ")
        ->groupBy('ot.id', 'ot.name')
        ->orderBy('ot.name')
        ->get()
        ->toArray();
    }

    public function getTimeEvolution(?User $user, Carbon $startUtc, Carbon $endUtc, string $groupBy = 'day', array $filters = []): array
    {
        $query = DB::table('operations as o')
            ->where('o.status', 'ACTIVE')
            ->whereBetween('o.effective_at', [$startUtc, $endUtc]);

        if ($user) {
            $query->where('o.user_id', $user->id);
        }

        if (!empty($filters['agent_id'])) {
            $query->join('operation_types as ot', 'o.operation_type_id', '=', 'ot.id');
        }

        $this->applyAdminFilters($query, $filters);

        $dateExpression = $this->getDateExpression($groupBy);

        return $query->selectRaw("
            {$dateExpression} as date_label,
            COUNT(*) as count,
            COALESCE(SUM(o.amount), 0) as total_amount,
            COALESCE(SUM(CASE WHEN o.cash_delta > 0 THEN 1 ELSE 0 END), 0) as cash_in_count,
            COALESCE(SUM(CASE WHEN o.cash_delta < 0 THEN 1 ELSE 0 END), 0) as cash_out_count
        ")
        ->groupBy('date_label')
        ->orderBy('date_label')
        ->get()
        ->toArray();
    }

    public function getOperatorComparison(Carbon $startUtc, Carbon $endUtc, array $operatorIds = [], int $limit = 10, int $offset = 0): array
    {
        $query = DB::table('operations as o')
            ->join('operation_types as ot', 'o.operation_type_id', '=', 'ot.id')
            ->join('users as u', 'o.user_id', '=', 'u.id')
            ->where('o.status', 'ACTIVE')
            ->whereBetween('o.effective_at', [$startUtc, $endUtc])
            ->where('u.role', 'OPERADOR');

        if (!empty($operatorIds)) {
            $query->whereIn('u.id', $operatorIds);
        }

        return $query->selectRaw("
            u.id, u.username_normalized,
            COUNT(*) as operation_count,
            COALESCE(SUM(o.amount), 0) as gross_amount,
            COALESCE(SUM(CASE WHEN o.cash_delta > 0 THEN o.cash_delta ELSE 0 END), 0) as cash_in,
            COALESCE(SUM(CASE WHEN o.cash_delta < 0 THEN ABS(o.cash_delta) ELSE 0 END), 0) as cash_out
        ")
        ->groupBy('u.id', 'u.username_normalized')
        ->orderByRaw('COALESCE(SUM(o.amount), 0) DESC')
        ->limit($limit)
        ->offset($offset)
        ->get()
        ->toArray();
    }

    public function getOperatorComparisonCount(Carbon $startUtc, Carbon $endUtc, array $operatorIds = []): int
    {
        $sub = DB::table('operations as o')
            ->join('users as u', 'o.user_id', '=', 'u.id')
            ->where('o.status', 'ACTIVE')
            ->whereBetween('o.effective_at', [$startUtc, $endUtc])
            ->where('u.role', 'OPERADOR');

        if (!empty($operatorIds)) {
            $sub->whereIn('u.id', $operatorIds);
        }

        return $sub->distinct()->count('u.id');
    }

    public function getAdminMetrics(Carbon $startUtc, Carbon $endUtc, array $filters = [], bool $includeAnnulled = false): object
    {
        $query = DB::table('operations as o')
            ->join('operation_types as ot', 'o.operation_type_id', '=', 'ot.id');

        if (!$includeAnnulled) {
            $query->where('o.status', 'ACTIVE');
        }

        $query->whereBetween('o.effective_at', [$startUtc, $endUtc]);

        $this->applyAdminFilters($query, $filters);

        return $query->selectRaw("
            COUNT(*) as operation_count,
            COALESCE(SUM(o.amount), 0) as gross_amount,
            COALESCE(SUM(CASE WHEN o.cash_delta > 0 THEN o.cash_delta ELSE 0 END), 0) as cash_in,
            COALESCE(SUM(CASE WHEN o.cash_delta < 0 THEN ABS(o.cash_delta) ELSE 0 END), 0) as cash_out
        ")
        ->first();
    }

    public function getAdminTypeDistribution(Carbon $startUtc, Carbon $endUtc, array $filters = [], bool $includeAnnulled = false): array
    {
        $query = DB::table('operations as o')
            ->join('operation_types as ot', 'o.operation_type_id', '=', 'ot.id');

        if (!$includeAnnulled) {
            $query->where('o.status', 'ACTIVE');
        }

        $query->whereBetween('o.effective_at', [$startUtc, $endUtc]);

        $this->applyAdminFilters($query, $filters);

        return $query->selectRaw("
            ot.name, COUNT(*) as count, COALESCE(SUM(o.amount), 0) as total_amount
        ")
        ->groupBy('ot.id', 'ot.name')
        ->orderBy('ot.name')
        ->get()
        ->toArray();
    }

    public function getAdminTimeEvolution(Carbon $startUtc, Carbon $endUtc, string $groupBy = 'day', array $filters = [], bool $includeAnnulled = false): array
    {
        $query = DB::table('operations as o');

        if (!$includeAnnulled) {
            $query->where('o.status', 'ACTIVE');
        }

        $query->whereBetween('o.effective_at', [$startUtc, $endUtc]);

        if (!empty($filters['agent_id'])) {
            $query->join('operation_types as ot', 'o.operation_type_id', '=', 'ot.id');
        }

        $this->applyAdminFilters($query, $filters);

        $dateExpression = $this->getDateExpression($groupBy);

        return $query->selectRaw("
            {$dateExpression} as date_label,
            COUNT(*) as count,
            COALESCE(SUM(o.amount), 0) as total_amount,
            COALESCE(SUM(CASE WHEN o.cash_delta > 0 THEN 1 ELSE 0 END), 0) as cash_in_count,
            COALESCE(SUM(CASE WHEN o.cash_delta < 0 THEN 1 ELSE 0 END), 0) as cash_out_count
        ")
        ->groupBy('date_label')
        ->orderBy('date_label')
        ->get()
        ->toArray();
    }

    public function getRecentOperations(Carbon $startUtc, Carbon $endUtc, array $filters = [], bool $includeAnnulled = false, int $perPage = 25): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = DB::table('operations as o')
            ->join('operation_types as ot', 'o.operation_type_id', '=', 'ot.id')
            ->join('agents as a', 'o.agent_id', '=', 'a.id')
            ->join('users as u', 'o.user_id', '=', 'u.id')
            ->whereBetween('o.effective_at', [$startUtc, $endUtc]);

        if (!$includeAnnulled) {
            $query->where('o.status', 'ACTIVE');
        }

        $this->applyAdminFilters($query, $filters);

        return $query->select([
                'o.id', 'o.amount', 'o.currency', 'o.status', 'o.effective_at',
                'ot.name as type_name',
                'a.code as agent_code', 'a.name as agent_name',
                'u.username_normalized',
            ])
            ->orderByDesc('o.effective_at')
            ->orderByDesc('o.id')
            ->paginate($perPage);
    }

    private function applyAdminFilters($query, array $filters): void
    {
        if (!empty($filters['agent_id'])) {
            $query->where('o.agent_id', $filters['agent_id']);
        }

        if (!empty($filters['city'])) {
            $query->where('a.city', 'like', '%' . $filters['city'] . '%');
        }

        if (!empty($filters['operator_id'])) {
            $query->where('o.user_id', $filters['operator_id']);
        }

        if (!empty($filters['operation_type_id'])) {
            $query->where('o.operation_type_id', $filters['operation_type_id']);
        }
    }

    private function getDateExpression(string $groupBy): string
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return match ($groupBy) {
                'day' => 'DATE(o.effective_at)',
                'week' => "strftime('%Y-%W', o.effective_at)",
                'month' => "strftime('%Y-%m', o.effective_at)",
                default => 'DATE(o.effective_at)',
            };
        }

        if ($driver === 'pgsql') {
            return match ($groupBy) {
                'day' => "TO_CHAR(o.effective_at, 'YYYY-MM-DD')",
                'week' => "TO_CHAR(o.effective_at, 'IYYY-IW')",
                'month' => "TO_CHAR(o.effective_at, 'YYYY-MM')",
                default => "TO_CHAR(o.effective_at, 'YYYY-MM-DD')",
            };
        }

        return match ($groupBy) {
            'day' => 'DATE(o.effective_at)',
            'week' => "DATE_FORMAT(o.effective_at, '%Y-%u')",
            'month' => "DATE_FORMAT(o.effective_at, '%Y-%m')",
            default => 'DATE(o.effective_at)',
        };
    }
}
