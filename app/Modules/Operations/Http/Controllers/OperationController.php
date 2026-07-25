<?php

namespace App\Modules\Operations\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Operations\Application\Actions\RegisterOperation;
use App\Modules\Operations\Application\Actions\ListOperations;
use App\Modules\Operations\Application\Actions\AnnulOperation;
use App\Modules\Operations\Http\Requests\RegisterOperationRequest;
use App\Modules\Operations\Http\Requests\AnnulOperationRequest;
use App\Modules\Operations\Models\Operation;
use App\Modules\Operations\Models\OperationType;
use App\Modules\Agents\Models\UserAgentAssignment;
use App\Modules\Audit\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OperationController extends Controller
{
    public function index(Request $request, ListOperations $listOperations): View
    {
        Gate::authorize('viewAny', Operation::class);

        $user = auth()->user();
        $isAdmin = $user->role->value === 'ADMINISTRADOR_PROPIETARIO';

        $operations = $listOperations->execute(
            $request->only(['code', 'customer_name', 'amount', 'agent_id', 'operation_type_id', 'status', 'user_id', 'date_from', 'date_to']),
            $isAdmin,
            $user->id,
            $user->organization_id,
        );

        $agents = collect();
        $types = collect();

        if ($isAdmin) {
            $agents = \App\Modules\Agents\Models\Agent::where('organization_id', $user->organization_id)->orderBy('code')->get();
        } else {
            $agents = \App\Modules\Agents\Models\Agent::whereHas('activeAssignments', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->orderBy('code')->get();
        }

        $types = OperationType::where('organization_id', $user->organization_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $summaryBase = DB::table('operations')
            ->where('operations.organization_id', $user->organization_id)
            ->where('operations.status', 'ACTIVE');

        if (! $isAdmin) {
            $summaryBase->where('operations.user_id', $user->id);
        }

        if ($request->filled('code')) {
            $summaryBase->where('operations.internal_code', 'LIKE', '%' . $request->input('code') . '%');
        }
        if ($request->filled('customer_name')) {
            $summaryBase->where('operations.customer_name', 'LIKE', '%' . $request->input('customer_name') . '%');
        }
        if ($request->filled('amount')) {
            $summaryBase->where('operations.amount', (float) $request->input('amount'));
        }
        if ($request->filled('agent_id')) {
            $summaryBase->where('operations.agent_id', $request->input('agent_id'));
        }
        if ($request->filled('operation_type_id')) {
            $summaryBase->where('operations.operation_type_id', $request->input('operation_type_id'));
        }
        if ($request->filled('date_from')) {
            $summaryBase->whereDate('operations.effective_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $summaryBase->whereDate('operations.effective_at', '<=', $request->input('date_to'));
        }

        $summaryCount = (clone $summaryBase)->selectRaw(
            'COUNT(*) as total_ops, COALESCE(SUM(operations.amount), 0) as total_amount'
        )->first();

        $summaryCash = (clone $summaryBase)
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN operations.cash_delta > 0 THEN operations.cash_delta ELSE 0 END), 0) as total_cash_in,
                 COALESCE(SUM(CASE WHEN operations.cash_delta < 0 THEN ABS(operations.cash_delta) ELSE 0 END), 0) as total_cash_out,
                 COALESCE(SUM(CASE WHEN operations.digital_delta > 0 THEN operations.digital_delta ELSE 0 END), 0) as total_digital_in,
                 COALESCE(SUM(CASE WHEN operations.digital_delta < 0 THEN ABS(operations.digital_delta) ELSE 0 END), 0) as total_digital_out"
            )->first();

        $summary = [
            'total_ops' => $summaryCount->total_ops ?? 0,
            'total_amount' => 'S/ ' . number_format((float) ($summaryCount->total_amount ?? 0), 2),
            'total_cash_in' => 'S/ ' . number_format((float) ($summaryCash->total_cash_in ?? 0), 2),
            'total_cash_out' => 'S/ ' . number_format((float) ($summaryCash->total_cash_out ?? 0), 2),
            'net_movement' => 'S/ ' . number_format((float) (($summaryCash->total_cash_in ?? 0) - ($summaryCash->total_cash_out ?? 0)), 2),
        ];

        return view('operations.index', compact('operations', 'agents', 'types', 'summary'));
    }

    public function create(): View
    {
        Gate::authorize('register', Operation::class);

        $user = auth()->user();

        $assignments = UserAgentAssignment::with('agent')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        $types = OperationType::where('organization_id', $user->organization_id)
            ->where('is_active', true)
            ->orderBy('sort_order')->orderBy('name')
            ->get();

        $idempotencyKey = hash('sha256', Str::uuid()->toString() . microtime());

        $agent = $assignments->count() === 1 ? $assignments->first()->agent : null;

        return view('operations.create', compact('assignments', 'types', 'idempotencyKey', 'agent'));
    }

    public function store(RegisterOperationRequest $request, RegisterOperation $registerOperation): RedirectResponse
    {
        Gate::authorize('register', Operation::class);

        $existing = Operation::where('idempotency_key', $request->input('idempotency_key'))->first();

        if ($existing) {
            return redirect()->route('operations.show', $existing)
                ->with('status', 'Esta operación ya fue registrada previamente.')
                ->with('idempotent', true);
        }

        try {
            $operation = $registerOperation->execute(
                $request->validated(),
                auth()->id(),
                auth()->user()->organization_id,
            );

            AuditLog::create([
                'correlation_id' => (string) Str::uuid(),
                'created_at' => now(),
                'organization_id' => $operation->organization_id,
                'actor_user_id' => auth()->id(),
                'action' => 'operation.created',
                'entity_type' => Operation::class,
                'entity_id' => $operation->id,
                'before_values' => null,
                'after_values' => $operation->only(['amount', 'currency', 'status', 'effective_at']),
                'occurred_at' => now(),
            ]);

            return redirect()->route('operations.show', $operation)
                ->with('status', 'Operación registrada correctamente.');
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            $existingByKey = Operation::where('idempotency_key', $request->input('idempotency_key'))->first();
            if ($existingByKey) {
                return redirect()->route('operations.show', $existingByKey)
                    ->with('status', 'Esta operación ya fue registrada previamente.')
                    ->with('idempotent', true);
            }
            throw $e;
        }
    }

    public function show(Operation $operation): View
    {
        Gate::authorize('view', $operation);

        $operation->load(['agent', 'operationType', 'user', 'annulledBy']);

        return view('operations.show', compact('operation'));
    }

    public function annul(AnnulOperationRequest $request, Operation $operation, AnnulOperation $annulOperation): RedirectResponse
    {
        Gate::authorize('annul', $operation);

        $user = auth()->user();
        $isAdmin = $user->role->value === 'ADMINISTRADOR_PROPIETARIO';

        try {
            $annulOperation->execute(
                $operation,
                $request->input('reason'),
                $user->id,
                $isAdmin,
            );

            return redirect()->route('operations.show', $operation)
                ->with('status', 'Operación anulada correctamente.');
        } catch (\RuntimeException $e) {
            return redirect()->route('operations.show', $operation)
                ->withErrors(['annulment' => $e->getMessage()]);
        }
    }
}
