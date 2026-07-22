<?php

namespace App\Modules\Operations\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Operations\Http\Requests\OperationTypeRequest;
use App\Modules\Operations\Models\OperationType;
use App\Modules\BankingNetwork\Models\Bank;
use App\Modules\Audit\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OperationTypeController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', OperationType::class);

        $orgId = auth()->user()->organization_id;

        $query = OperationType::with('bank')
            ->where('organization_id', $orgId);

        if ($request->filled('bank_id')) {
            $query->where('bank_id', $request->input('bank_id'));
        } elseif ($request->has('general') && $request->input('general') === '1') {
            $query->whereNull('bank_id');
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $types = $query->orderBy('name')->paginate(20)->withQueryString();
        $banks = Bank::where('organization_id', $orgId)->where('is_active', true)->orderBy('name')->get();

        return view('operations.types.index', compact('types', 'banks'));
    }

    public function create(): View
    {
        Gate::authorize('create', OperationType::class);

        $orgId = auth()->user()->organization_id;
        $banks = Bank::where('organization_id', $orgId)->where('is_active', true)->orderBy('name')->get();

        return view('operations.types.form', [
            'type' => new OperationType(),
            'banks' => $banks,
        ]);
    }

    public function store(OperationTypeRequest $request): RedirectResponse
    {
        Gate::authorize('create', OperationType::class);

        $type = OperationType::create([
            'organization_id' => auth()->user()->organization_id,
            'bank_id' => $request->input('bank_id'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'cash_direction' => $request->input('cash_direction'),
            'is_active' => true,
        ]);

        AuditLog::create([
            'correlation_id' => (string) Str::uuid(),
            'created_at' => now(),
            'organization_id' => auth()->user()->organization_id,
            'actor_user_id' => auth()->id(),
            'action' => 'operation_type.created',
            'entity_type' => OperationType::class,
            'entity_id' => $type->id,
            'before_values' => null,
            'after_values' => $type->only(['name', 'bank_id', 'cash_direction']),
            'occurred_at' => now(),
        ]);

        return redirect()->route('admin.operation-types.index')
            ->with('status', 'Tipo de operación creado correctamente.');
    }

    public function edit(OperationType $type): View
    {
        Gate::authorize('update', $type);

        $orgId = auth()->user()->organization_id;
        $banks = Bank::where('organization_id', $orgId)->where('is_active', true)->orderBy('name')->get();

        return view('operations.types.form', [
            'type' => $type,
            'banks' => $banks,
        ]);
    }

    public function update(OperationTypeRequest $request, OperationType $type): RedirectResponse
    {
        Gate::authorize('update', $type);

        $before = $type->only(['name', 'bank_id', 'description', 'cash_direction']);

        $type->update([
            'bank_id' => $request->input('bank_id'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'cash_direction' => $request->input('cash_direction'),
        ]);

        AuditLog::create([
            'correlation_id' => (string) Str::uuid(),
            'created_at' => now(),
            'organization_id' => auth()->user()->organization_id,
            'actor_user_id' => auth()->id(),
            'action' => 'operation_type.updated',
            'entity_type' => OperationType::class,
            'entity_id' => $type->id,
            'before_values' => $before,
            'after_values' => $type->only(['name', 'bank_id', 'cash_direction']),
            'occurred_at' => now(),
        ]);

        return redirect()->route('admin.operation-types.index')
            ->with('status', 'Tipo de operación actualizado correctamente.');
    }

    public function destroy(OperationType $type): RedirectResponse
    {
        Gate::authorize('delete', $type);

        if (! $type->is_active) {
            return redirect()->route('admin.operation-types.index')
                ->with('status', 'El tipo ya está inactivo.');
        }

        $before = $type->only(['is_active']);

        $type->update([
            'is_active' => false,
            'deactivated_at' => now(),
        ]);

        AuditLog::create([
            'correlation_id' => (string) Str::uuid(),
            'created_at' => now(),
            'organization_id' => auth()->user()->organization_id,
            'actor_user_id' => auth()->id(),
            'action' => 'operation_type.deactivated',
            'entity_type' => OperationType::class,
            'entity_id' => $type->id,
            'before_values' => $before,
            'after_values' => ['is_active' => false],
            'occurred_at' => now(),
        ]);

        return redirect()->route('admin.operation-types.index')
            ->with('status', 'Tipo de operación desactivado correctamente.');
    }
}
