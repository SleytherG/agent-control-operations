<?php

namespace App\Modules\DailyClosing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Agents\Models\Agent;
use App\Modules\Agents\Models\UserAgentAssignment;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\DailyClosing\Application\Actions\CalculateClosing;
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

        $query = DailyClosure::with(['agent', 'confirmedBy', 'reopenedBy'])
            ->where('organization_id', $user->organization_id);

        if (! $isAdmin) {
            $assignedAgentIds = UserAgentAssignment::where('user_id', $user->id)
                ->where('is_active', true)
                ->pluck('agent_id');

            $query->whereIn('agent_id', $assignedAgentIds);
        }

        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
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
            $agents = Agent::where('organization_id', $user->organization_id)
                ->orderBy('code')->get();
        } else {
            $agents = Agent::whereHas('activeAssignments', function ($q) use ($user) {
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
            $agents = Agent::where('organization_id', $user->organization_id)
                ->where('is_active', true)
                ->orderBy('code')
                ->get();
        } else {
            $agents = Agent::whereHas('activeAssignments', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('is_active', true)
                ->orderBy('code')
                ->get();
        }

        return view('daily-closing.create', compact('agents'));
    }

    public function store(GenerateClosingRequest $request): RedirectResponse
    {
        $agentId = (int) $request->input('agent_id', $request->input('bank_agent_id'));

        Gate::authorize('generate', [DailyClosure::class, $agentId]);

        $businessDate = $request->input('business_date');
        $user = auth()->user();
        $organizationId = (int) $user->organization_id;

        $openingCash = $request->input('opening_cash', 0);
        $openingDigital = $request->input('opening_digital', 0);

        $existingClosure = DailyClosure::where('agent_id', $agentId)
            ->where('business_date', $businessDate)
            ->whereIn('status', [DailyClosure::STATUS_ACTIVO, 'BORRADOR'])
            ->first();

        if ($existingClosure) {
            return redirect()->route('daily-closures.show', $existingClosure)
                ->with('status', 'Ya existe un cierre activo o borrador para este agente y fecha.');
        }

        try {
            return DB::transaction(function () use ($agentId, $businessDate, $openingCash, $openingDigital, $user, $organizationId) {
                $closure = DailyClosure::create([
                    'organization_id' => $organizationId,
                    'agent_id' => $agentId,
                    'business_date' => $businessDate,
                    'status' => 'BORRADOR',
                    'opening_cash' => $openingCash,
                    'opening_digital' => $openingDigital,
                ]);

                AuditLog::create([
                    'correlation_id' => (string) Str::uuid(),
                    'created_at' => now(),
                    'organization_id' => $organizationId,
                    'actor_user_id' => $user->id,
                    'action' => 'daily_closure.created',
                    'entity_type' => DailyClosure::class,
                    'entity_id' => $closure->id,
                    'after_values' => $closure->only(['agent_id', 'business_date', 'opening_cash', 'opening_digital', 'status']),
                    'occurred_at' => now(),
                ]);

                return redirect()->route('daily-closures.show', $closure)
                    ->with('status', 'Apertura diaria registrada correctamente.');
            });
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            return redirect()->route('daily-closures.index')
                ->withErrors(['store' => 'Violación de restricción única. Ya existe un cierre activo para este agente y fecha.']);
        }
    }

    public function show(DailyClosure $closure, CalculateClosing $calculateClosing): View
    {
        Gate::authorize('view', $closure);

        $closure->load(['agent', 'confirmedBy', 'reopenedBy']);

        $calculateClosing->execute($closure);

        $closure->refresh();

        $breakdownByType = DB::table('daily_closure_operations as dco')
            ->join('operations as o', 'dco.operation_id', '=', 'o.id')
            ->join('operation_types as ot', 'o.operation_type_id', '=', 'ot.id')
            ->where('dco.daily_closure_id', $closure->id)
            ->select('ot.name', DB::raw('COUNT(*) as operation_count'), DB::raw('COALESCE(SUM(o.amount), 0) as total_amount'))
            ->groupBy('ot.name')
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

        $closureOperations = Operation::with(['operationType', 'user'])
            ->whereIn('id', function ($query) use ($closure) {
                $query->select('operation_id')
                    ->from('daily_closure_operations')
                    ->where('daily_closure_id', $closure->id);
            })
            ->orderBy('effective_at', 'desc')
            ->get();

        return view('daily-closing.show', compact(
            'closure', 'breakdownByType', 'breakdownByOperator', 'closureOperations'
        ));
    }

    public function confirm(Request $request, DailyClosure $closure): RedirectResponse
    {
        Gate::authorize('confirm', $closure);

        if (! in_array($closure->status, [DailyClosure::STATUS_ACTIVO, DailyClosure::STATUS_REABIERTO, 'BORRADOR', 'PRESENTADO'])) {
            return redirect()->route('daily-closures.show', $closure)
                ->withErrors(['confirm' => 'El cierre no se puede confirmar en su estado actual.']);
        }

        $hasDifferences = ($closure->cash_difference != 0 || $closure->digital_difference != 0);

        if ($hasDifferences && ! $request->filled('confirm_reason')) {
            return redirect()->route('daily-closures.show', $closure)
                ->withErrors(['confirm' => 'Debe proporcionar un motivo para confirmar con diferencias.']);
        }

        $user = auth()->user();
        $beforeStatus = $closure->status;

        $closure->update([
            'status' => DailyClosure::STATUS_CONFIRMADO,
            'confirmed_by' => $user->id,
            'confirmed_at' => now(),
        ]);

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
            'reason' => $request->input('confirm_reason'),
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

        $closure->update([
            'status' => DailyClosure::STATUS_REABIERTO,
            'reopened_by' => $user->id,
            'reopened_at' => now(),
            'reopen_reason' => $reason,
        ]);

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
