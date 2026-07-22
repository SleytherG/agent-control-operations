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
            ->join('operation_types as ot', 'o.operation_type_id', '=', 'ot.id')
            ->where('o.status', 'ACTIVE')
            ->where('o.user_id', $user->id)
            ->whereBetween('o.effective_at', [$startUtc, $endUtc])
            ->selectRaw("
                COUNT(*) as operation_count,
                COALESCE(SUM(o.amount), 0) as gross_amount,
                COALESCE(SUM(CASE WHEN ot.cash_direction = 'ENTRADA' THEN o.amount ELSE 0 END), 0) as cash_in,
                COALESCE(SUM(CASE WHEN ot.cash_direction = 'SALIDA' THEN o.amount ELSE 0 END), 0) as cash_out,
                COALESCE(SUM(CASE WHEN ot.cash_direction = 'ENTRADA' THEN o.amount ELSE 0 END), 0)
                  - COALESCE(SUM(CASE WHEN ot.cash_direction = 'SALIDA' THEN o.amount ELSE 0 END), 0) as net_movement
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
            ot.name, ot.cash_direction, COUNT(*) as count, COALESCE(SUM(o.amount), 0) as total_amount
        ")
        ->groupBy('ot.id', 'ot.name', 'ot.cash_direction')
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

        if (!empty($filters['bank_id'])) {
            $query->join('operation_types as ot', 'o.operation_type_id', '=', 'ot.id');
        }

        $this->applyAdminFilters($query, $filters);

        $dateExpression = $this->getDateExpression($groupBy);

        return $query->selectRaw("
            {$dateExpression} as date_label, COUNT(*) as count, COALESCE(SUM(o.amount), 0) as total_amount
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
            COALESCE(SUM(CASE WHEN ot.cash_direction = 'ENTRADA' THEN o.amount ELSE 0 END), 0) as cash_in,
            COALESCE(SUM(CASE WHEN ot.cash_direction = 'SALIDA' THEN o.amount ELSE 0 END), 0) as cash_out,
            COALESCE(SUM(CASE WHEN ot.cash_direction = 'ENTRADA' THEN o.amount ELSE 0 END), 0)
              - COALESCE(SUM(CASE WHEN ot.cash_direction = 'SALIDA' THEN o.amount ELSE 0 END), 0) as net_movement
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
            COALESCE(SUM(CASE WHEN ot.cash_direction = 'ENTRADA' THEN o.amount ELSE 0 END), 0) as cash_in,
            COALESCE(SUM(CASE WHEN ot.cash_direction = 'SALIDA' THEN o.amount ELSE 0 END), 0) as cash_out,
            COALESCE(SUM(CASE WHEN ot.cash_direction = 'ENTRADA' THEN o.amount ELSE 0 END), 0)
              - COALESCE(SUM(CASE WHEN ot.cash_direction = 'SALIDA' THEN o.amount ELSE 0 END), 0) as net_movement
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
            ot.name, ot.cash_direction, COUNT(*) as count, COALESCE(SUM(o.amount), 0) as total_amount
        ")
        ->groupBy('ot.id', 'ot.name', 'ot.cash_direction')
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

        if (!empty($filters['bank_id'])) {
            $query->join('operation_types as ot', 'o.operation_type_id', '=', 'ot.id');
        }

        $this->applyAdminFilters($query, $filters);

        $dateExpression = $this->getDateExpression($groupBy);

        return $query->selectRaw("
            {$dateExpression} as date_label, COUNT(*) as count, COALESCE(SUM(o.amount), 0) as total_amount
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
            ->join('bank_agents as ba', 'o.bank_agent_id', '=', 'ba.id')
            ->join('users as u', 'o.user_id', '=', 'u.id')
            ->leftJoin('stores as s', 'ba.store_id', '=', 's.id')
            ->whereBetween('o.effective_at', [$startUtc, $endUtc]);

        if (!$includeAnnulled) {
            $query->where('o.status', 'ACTIVE');
        }

        $this->applyAdminFilters($query, $filters);

        return $query->select([
                'o.id', 'o.amount', 'o.currency', 'o.status', 'o.effective_at',
                'ot.name as type_name', 'ot.cash_direction',
                'ba.code as agent_code', 's.name as store_name',
                'u.username_normalized',
            ])
            ->orderByDesc('o.effective_at')
            ->orderByDesc('o.id')
            ->paginate($perPage);
    }

    private function applyAdminFilters($query, array $filters): void
    {
        $needsStoreJoin = !empty($filters['region_id']) || !empty($filters['province_id']) || !empty($filters['district_id']);

        if (!empty($filters['region_id'])) {
            $query->join('stores as _fs', '_fs.id', '=', 'o.store_id');
            $query->join('districts as _fd', '_fd.id', '=', '_fs.district_id');
            $query->join('provinces as _fp', '_fp.id', '=', '_fd.province_id');
            $query->where('_fp.region_id', $filters['region_id']);
        } elseif (!empty($filters['province_id'])) {
            $query->join('stores as _fs', '_fs.id', '=', 'o.store_id');
            $query->join('districts as _fd', '_fd.id', '=', '_fs.district_id');
            $query->where('_fd.province_id', $filters['province_id']);
        } elseif (!empty($filters['district_id'])) {
            $query->join('stores as _fs', '_fs.id', '=', 'o.store_id');
            $query->where('_fs.district_id', $filters['district_id']);
        }

        if (!empty($filters['store_id'])) {
            $query->where('o.store_id', $filters['store_id']);
        }

        if (!empty($filters['bank_id'])) {
            $query->where('ot.bank_id', $filters['bank_id']);
        }

        if (!empty($filters['bank_agent_id'])) {
            $query->where('o.bank_agent_id', $filters['bank_agent_id']);
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

        return match ($groupBy) {
            'day' => 'DATE(o.effective_at)',
            'week' => "DATE_FORMAT(o.effective_at, '%Y-%u')",
            'month' => "DATE_FORMAT(o.effective_at, '%Y-%m')",
            default => 'DATE(o.effective_at)',
        };
    }
}
