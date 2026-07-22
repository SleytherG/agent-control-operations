<?php

namespace App\Modules\Reporting\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BankingNetwork\Models\Bank;
use App\Modules\BankingNetwork\Models\BankAgent;
use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Operations\Models\OperationType;
use App\Modules\Organization\Models\District;
use App\Modules\Organization\Models\Province;
use App\Modules\Organization\Models\Region;
use App\Modules\Organization\Models\Store;
use App\Modules\Reporting\Http\Requests\DashboardFilterRequest;
use App\Modules\Reporting\Services\DashboardQueryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardQueryService $queryService,
    ) {}

    public function operatorDashboard(Request $request): View
    {
        Gate::authorize('viewOperatorDashboard');

        $user = auth()->user();
        $period = $request->input('period', 'month');
        $dateStr = $request->input('date', now()->format('Y-m-d'));

        [$startUtc, $endUtc] = $this->resolvePeriod($period, $dateStr);

        $metrics = $this->queryService->getOperatorMetrics($user, $startUtc, $endUtc);

        if ($metrics->operation_count === 0) {
            return view('reporting.operator-dashboard', [
                'metrics' => (object) [
                    'operation_count' => 0,
                    'gross_amount' => '0.00',
                    'cash_in' => '0.00',
                    'cash_out' => '0.00',
                    'net_movement' => '0.00',
                ],
                'typeDistribution' => [],
                'timeEvolution' => [],
                'period' => $period,
                'date' => $dateStr,
            ]);
        }

        $typeDistribution = $this->queryService->getTypeDistribution($user, $startUtc, $endUtc);

        $groupBy = in_array($period, ['day', 'week']) ? 'day' : 'month';
        $timeEvolution = $this->queryService->getTimeEvolution($user, $startUtc, $endUtc, $groupBy);

        return view('reporting.operator-dashboard', compact(
            'metrics', 'typeDistribution', 'timeEvolution', 'period',
        ) + ['date' => $dateStr]);
    }

    public function adminDashboard(DashboardFilterRequest $request): View
    {
        Gate::authorize('viewAdminDashboard');

        $user = auth()->user();

        $filters = $request->validated();
        $includeAnnulled = $request->boolean('include_annulled');

        [$startUtc, $endUtc] = $this->resolvePeriodFromRequest($request);

        $metrics = $this->queryService->getAdminMetrics($startUtc, $endUtc, $filters, $includeAnnulled);

        $isEmpty = $metrics->operation_count === 0;

        $typeDistribution = $isEmpty ? [] : $this->queryService->getAdminTypeDistribution($startUtc, $endUtc, $filters, $includeAnnulled);

        $period = $request->input('period', 'month');
        $groupBy = in_array($period, ['day', 'week']) ? 'day' : 'month';
        $timeEvolution = $isEmpty ? [] : $this->queryService->getAdminTimeEvolution($startUtc, $endUtc, $groupBy, $filters, $includeAnnulled);

        $operations = $isEmpty ? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 25) : $this->queryService->getRecentOperations($startUtc, $endUtc, $filters, $includeAnnulled);

        $regions = Region::where('organization_id', $user->organization_id)->orderBy('name')->get();
        $provinces = !empty($filters['region_id']) ? Province::where('region_id', $filters['region_id'])->orderBy('name')->get() : collect();
        $districts = !empty($filters['province_id']) ? District::where('province_id', $filters['province_id'])->orderBy('name')->get() : collect();
        $stores = Store::where('organization_id', $user->organization_id)->orderBy('name')->get();
        $banks = Bank::where('organization_id', $user->organization_id)->orderBy('name')->get();
        $bankAgents = BankAgent::where('organization_id', $user->organization_id)->orderBy('code')->get();
        $types = OperationType::where('organization_id', $user->organization_id)->where('is_active', true)->orderBy('name')->get();

        return view('reporting.admin-dashboard', compact(
            'metrics', 'typeDistribution', 'timeEvolution', 'operations',
            'filters', 'includeAnnulled', 'period', 'regions', 'provinces',
            'districts', 'stores', 'banks', 'bankAgents', 'types',
        ));
    }

    public function operatorComparison(DashboardFilterRequest $request): View
    {
        Gate::authorize('viewAdminDashboard');

        $user = auth()->user();

        [$startUtc, $endUtc] = $this->resolvePeriodFromRequest($request);

        $operatorIds = $request->input('operator_ids', []);
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);

        $total = $this->queryService->getOperatorComparisonCount($startUtc, $endUtc, $operatorIds);
        $offset = ($page - 1) * $perPage;

        $operators = $this->queryService->getOperatorComparison($startUtc, $endUtc, $operatorIds, $perPage, $offset);

        $allOperators = User::where('organization_id', $user->organization_id)
            ->where('role', Role::OPERADOR)
            ->orderBy('username_normalized')
            ->get();

        $period = $request->input('period', 'month');
        $date = $request->input('date', now()->format('Y-m-d'));

        return view('reporting.operator-comparison', [
            'operators' => $operators,
            'allOperators' => $allOperators,
            'selectedOperatorIds' => $operatorIds,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'period' => $period,
            'date' => $date,
            'dateFrom' => $request->input('date_from'),
            'dateTo' => $request->input('date_to'),
        ]);
    }

    private function resolvePeriod(string $period, string $dateStr): array
    {
        $limaDate = Carbon::parse($dateStr, 'America/Lima');

        [$limaStart, $limaEnd] = match ($period) {
            'day' => [
                $limaDate->copy()->startOfDay(),
                $limaDate->copy()->endOfDay(),
            ],
            'week' => [
                $limaDate->copy()->startOfWeek(Carbon::MONDAY),
                $limaDate->copy()->startOfWeek(Carbon::MONDAY)->addDays(6)->endOfDay(),
            ],
            'quarter' => [
                $limaDate->copy()->firstOfQuarter()->startOfDay(),
                $limaDate->copy()->lastOfQuarter()->endOfDay(),
            ],
            'semester' => [
                $this->firstOfSemester($limaDate->copy())->startOfDay(),
                $this->lastOfSemester($limaDate->copy())->endOfDay(),
            ],
            'year' => [
                $limaDate->copy()->startOfYear()->startOfDay(),
                $limaDate->copy()->endOfYear()->endOfDay(),
            ],
            default => [
                $limaDate->copy()->startOfMonth()->startOfDay(),
                $limaDate->copy()->endOfMonth()->endOfDay(),
            ],
        };

        return [$limaStart->copy()->setTimezone('UTC'), $limaEnd->copy()->setTimezone('UTC')];
    }

    private function resolvePeriodFromRequest(Request $request): array
    {
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $startUtc = Carbon::parse($request->input('date_from'), 'America/Lima')->startOfDay()->setTimezone('UTC');
            $endUtc = Carbon::parse($request->input('date_to'), 'America/Lima')->endOfDay()->setTimezone('UTC');
            return [$startUtc, $endUtc];
        }

        return $this->resolvePeriod(
            $request->input('period', 'month'),
            $request->input('date', now()->format('Y-m-d'))
        );
    }

    private function firstOfSemester(Carbon $date): Carbon
    {
        return $date->month <= 6
            ? $date->copy()->startOfYear()
            : $date->copy()->startOfYear()->addMonths(6);
    }

    private function lastOfSemester(Carbon $date): Carbon
    {
        return $date->month <= 6
            ? $date->copy()->startOfYear()->addMonths(6)->subDay()->endOfDay()
            : $date->copy()->endOfYear();
    }
}
