<?php

namespace App\Modules\DailyClosing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\BankingNetwork\Models\BankAgent;
use App\Modules\BankingNetwork\Models\UserBankAgentAssignment;
use App\Modules\DailyClosing\Http\Requests\GenerateClosingRequest;
use App\Modules\DailyClosing\Http\Requests\ReopenClosingRequest;
use App\Modules\DailyClosing\Models\DailyClosure;
use App\Modules\DailyClosing\Models\DailyClosureOperation;
use App\Modules\Operations\Models\Operation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DailyClosingController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', DailyClosure::class);

        $user = auth()->user();
        $isAdmin = $user->role->value === 'ADMINISTRADOR_PROPIETARIO';

        $query = DailyClosure::with(['bankAgent.store', 'bankAgent.bank', 'confirmedBy', 'reopenedBy'])
            ->where('organization_id', $user->organization_id);

        if (! $isAdmin) {
            $assignedAgentIds = UserBankAgentAssignment::where('user_id', $user->id)
                ->where('is_active', true)
                ->pluck('bank_agent_id');

            $query->whereIn('bank_agent_id', $assignedAgentIds);
        }

        if ($request->filled('bank_agent_id')) {
            $query->where('bank_agent_id', $request->bank_agent_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('business_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('business_date', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $closures = $query->orderBy('business_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(25)
            ->withQueryString();

        if ($isAdmin) {
            $agents = BankAgent::where('organization_id', $user->organization_id)
                ->orderBy('code')->get();
        } else {
            $agents = BankAgent::whereHas('activeAssignments', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->orderBy('code')->get();
        }

        return view('daily-closing.index', compact('closures', 'agents'));
    }

    public function create(): View
    {
        Gate::authorize('viewAny', DailyClosure::class);

        $user = auth()->user();
        $isAdmin = $user->role->value === 'ADMINISTRADOR_PROPIETARIO';

        if ($isAdmin) {
            $agents = BankAgent::with('store', 'bank')
                ->where('organization_id', $user->organization_id)
                ->where('is_active', true)
                ->orderBy('code')
                ->get();
        } else {
            $agents = BankAgent::with('store', 'bank')
                ->whereHas('activeAssignments', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->orderBy('code')
                ->get();
        }

        return view('daily-closing.create', compact('agents'));
    }

    public function store(GenerateClosingRequest $request): RedirectResponse
    {
        $bankAgentId = (int) $request->input('bank_agent_id');

        Gate::authorize('generate', [DailyClosure::class, $bankAgentId]);

        $businessDate = $request->input('business_date');
        $regenerate = $request->boolean('regenerate');
        $user = auth()->user();
        $organizationId = (int) $user->organization_id;

        $existingClosure = DailyClosure::where('bank_agent_id', $bankAgentId)
            ->where('business_date', $businessDate)
            ->where('status', DailyClosure::STATUS_ACTIVO)
            ->first();

        if ($existingClosure && ! $regenerate) {
            return redirect()->route('daily-closures.show', $existingClosure)
                ->with('status', 'Ya existe un cierre activo para este agente y fecha.');
        }

        try {
            return $this->executeStore($bankAgentId, $businessDate, $regenerate, $existingClosure, $organizationId);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            return redirect()->route('daily-closures.index')
                ->withErrors(['store' => 'Violación de restricción única. Ya existe un cierre activo para este agente y fecha.']);
        }
    }

    private function executeStore(int $bankAgentId, string $businessDate, bool $regenerate, ?DailyClosure $existingClosure, int $organizationId): RedirectResponse
    {
        $agent = BankAgent::with('store')->findOrFail($bankAgentId);
        $user = auth()->user();

        return DB::transaction(function () use ($bankAgentId, $businessDate, $regenerate, $existingClosure, $user, $organizationId, $agent) {
            if ($existingClosure && $regenerate) {
                DailyClosureOperation::where('daily_closure_id', $existingClosure->id)->delete();
                $closure = $existingClosure;
            } else {
                $closure = DailyClosure::create([
                    'organization_id' => $organizationId,
                    'store_id' => $agent->store_id,
                    'bank_agent_id' => $bankAgentId,
                    'business_date' => $businessDate,
                    'status' => DailyClosure::STATUS_ACTIVO,
                ]);
            }

            $startDate = $businessDate . ' 00:00:00';
            $endDate = date('Y-m-d', strtotime($businessDate . ' +1 day')) . ' 00:00:00';

            DB::statement('
                INSERT INTO daily_closure_operations (daily_closure_id, operation_id, created_at)
                SELECT ?, o.id, ?
                FROM operations o
                WHERE o.bank_agent_id = ?
                  AND o.status = ?
                  AND o.effective_at >= ?
                  AND o.effective_at < ?
            ', [$closure->id, now()->format('Y-m-d H:i:s.u'), $bankAgentId, Operation::STATUS_ACTIVE, $startDate, $endDate]);

            $metrics = DB::select('
                SELECT
                    COUNT(*) as operation_count,
                    COALESCE(SUM(o.amount), 0) as gross_amount,
                    COALESCE(SUM(CASE WHEN ot.cash_direction = ? THEN o.amount ELSE 0 END), 0) as cash_in,
                    COALESCE(SUM(CASE WHEN ot.cash_direction = ? THEN o.amount ELSE 0 END), 0) as cash_out,
                    CAST(COALESCE(SUM(CASE WHEN ot.cash_direction = ? THEN 1 ELSE 0 END), 0) AS INTEGER) as pending_confirm_count
                FROM operations o
                JOIN operation_types ot ON o.operation_type_id = ot.id
                WHERE o.bank_agent_id = ?
                  AND o.status = ?
                  AND o.effective_at >= ?
                  AND o.effective_at < ?
            ', ['ENTRADA', 'SALIDA', 'POR_CONFIRMAR', $bankAgentId, Operation::STATUS_ACTIVE, $startDate, $endDate]);

            $m = $metrics[0];
            $hasPendingConfirm = $m->pending_confirm_count > 0;
            $netMovement = bcsub((string) $m->cash_in, (string) $m->cash_out, 2);

            $closure->update([
                'operation_count' => $m->operation_count,
                'gross_amount' => $m->gross_amount,
                'cash_in' => $m->cash_in,
                'cash_out' => $m->cash_out,
                'net_movement' => $netMovement,
                'has_pending_confirm' => $hasPendingConfirm,
            ]);

            AuditLog::create([
                'correlation_id' => (string) Str::uuid(),
                'created_at' => now(),
                'organization_id' => $organizationId,
                'actor_user_id' => $user->id,
                'action' => $regenerate ? 'daily_closure.regenerated' : 'daily_closure.generated',
                'entity_type' => DailyClosure::class,
                'entity_id' => $closure->id,
                'before_values' => null,
                'after_values' => $closure->only(['bank_agent_id', 'business_date', 'operation_count', 'gross_amount', 'cash_in', 'cash_out', 'net_movement', 'status', 'has_pending_confirm']),
                'occurred_at' => now(),
            ]);

            return redirect()->route('daily-closures.show', $closure)
                ->with('status', $regenerate ? 'Cierre regenerado correctamente.' : 'Cierre generado correctamente.');
        });
    }

    public function show(DailyClosure $closure): View
    {
        Gate::authorize('view', $closure);

        $closure->load(['bankAgent.store', 'bankAgent.bank', 'confirmedBy', 'reopenedBy']);

        $breakdownByType = DB::table('daily_closure_operations as dco')
            ->join('operations as o', 'dco.operation_id', '=', 'o.id')
            ->join('operation_types as ot', 'o.operation_type_id', '=', 'ot.id')
            ->where('dco.daily_closure_id', $closure->id)
            ->select('ot.name', 'ot.cash_direction', DB::raw('COUNT(*) as operation_count'), DB::raw('COALESCE(SUM(o.amount), 0) as total_amount'))
            ->groupBy('ot.name', 'ot.cash_direction')
            ->orderBy('ot.name')
            ->get();

        $breakdownByOperator = DB::table('daily_closure_operations as dco')
            ->join('operations as o', 'dco.operation_id', '=', 'o.id')
            ->join('users as u', 'o.user_id', '=', 'u.id')
            ->where('dco.daily_closure_id', $closure->id)
            ->select('u.username_normalized', DB::raw('COUNT(*) as operation_count'), DB::raw('COALESCE(SUM(o.amount), 0) as total_amount'))
            ->groupBy('u.username_normalized')
            ->orderBy('u.username_normalized')
            ->get();

        $annulledOperations = Operation::with(['operationType', 'user', 'annulledBy'])
            ->where('bank_agent_id', $closure->bank_agent_id)
            ->where('status', Operation::STATUS_ANNULLED)
            ->whereDate('effective_at', $closure->business_date)
            ->orderBy('annulled_at', 'desc')
            ->get();

        $closureOperations = Operation::with(['operationType', 'user'])
            ->whereIn('id', function ($query) use ($closure) {
                $query->select('operation_id')
                    ->from('daily_closure_operations')
                    ->where('daily_closure_id', $closure->id);
            })
            ->orderBy('effective_at', 'desc')
            ->get();

        return view('daily-closing.show', compact(
            'closure', 'breakdownByType', 'breakdownByOperator',
            'annulledOperations', 'closureOperations'
        ));
    }

    public function confirm(DailyClosure $closure): RedirectResponse
    {
        Gate::authorize('confirm', $closure);

        if (! $closure->isActivo() && ! $closure->isReabierto()) {
            return redirect()->route('daily-closures.show', $closure)
                ->withErrors(['confirm' => 'Solo se puede confirmar un cierre en estado ACTIVO o REABIERTO.']);
        }

        $user = auth()->user();
        $beforeStatus = $closure->status;

        $closure->confirm($user->id);

        AuditLog::create([
            'correlation_id' => (string) Str::uuid(),
            'created_at' => now(),
            'organization_id' => $closure->organization_id,
            'actor_user_id' => $user->id,
            'action' => 'daily_closure.confirmed',
            'entity_type' => DailyClosure::class,
            'entity_id' => $closure->id,
            'before_values' => ['status' => $beforeStatus],
            'after_values' => $closure->only(['status', 'confirmed_by', 'confirmed_at']),
            'occurred_at' => now(),
        ]);

        return redirect()->route('daily-closures.show', $closure)
            ->with('status', 'Cierre confirmado correctamente.');
    }

    public function reopen(ReopenClosingRequest $request, DailyClosure $closure): RedirectResponse
    {
        Gate::authorize('reopen', $closure);

        if (! $closure->isConfirmado()) {
            return redirect()->route('daily-closures.show', $closure)
                ->withErrors(['reopen' => 'Solo se puede reabrir un cierre en estado CONFIRMADO.']);
        }

        $user = auth()->user();
        $reason = $request->input('reason');
        $beforeStatus = $closure->status;

        $closure->reopen($user->id, $reason);

        AuditLog::create([
            'correlation_id' => (string) Str::uuid(),
            'created_at' => now(),
            'organization_id' => $closure->organization_id,
            'actor_user_id' => $user->id,
            'action' => 'daily_closure.reopened',
            'entity_type' => DailyClosure::class,
            'entity_id' => $closure->id,
            'before_values' => ['status' => $beforeStatus],
            'after_values' => $closure->only(['status', 'reopened_by', 'reopened_at', 'reopen_reason']),
            'occurred_at' => now(),
        ]);

        return redirect()->route('daily-closures.show', $closure)
            ->with('status', 'Cierre reabierto correctamente.');
    }
}
