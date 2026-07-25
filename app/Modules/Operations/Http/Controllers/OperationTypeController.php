<?php

namespace App\Modules\Operations\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Operations\Http\Requests\OperationTypeRequest;
use App\Modules\Operations\Models\OperationType;
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

        $query = OperationType::where('organization_id', $orgId);

        if ($request->filled('name')) {
            $query->where('name', 'LIKE', '%' . $request->input('name') . '%');
        }

        if ($request->filled('description')) {
            $query->where('description', 'LIKE', '%' . $request->input('description') . '%');
        }

        if ($request->filled('cash_multiplier')) {
            $query->where('cash_multiplier', (int) $request->input('cash_multiplier'));
        }

        if ($request->filled('digital_multiplier')) {
            $query->where('digital_multiplier', (int) $request->input('digital_multiplier'));
        }

        if ($request->filled('sort_order')) {
            $query->where('sort_order', (int) $request->input('sort_order'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $types = $query->orderBy('sort_order')->orderBy('name')->paginate(20)->withQueryString();

        return view('operations.types.index', compact('types'));
    }

    public function create(): View
    {
        Gate::authorize('create', OperationType::class);

        return view('operations.types.form', [
            'type' => new OperationType(),
        ]);
    }

    public function store(OperationTypeRequest $request): RedirectResponse
    {
        Gate::authorize('create', OperationType::class);

        $type = OperationType::create([
            'organization_id' => auth()->user()->organization_id,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'cash_multiplier' => $request->input('cash_multiplier'),
            'digital_multiplier' => $request->input('digital_multiplier'),
            'sort_order' => $request->input('sort_order', 0),
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
            'after_values' => $type->only(['name', 'cash_multiplier', 'digital_multiplier']),
            'occurred_at' => now(),
        ]);

        return redirect()->route('admin.operation-types.index')
            ->with('status', 'Tipo de operación creado correctamente.');
    }

    public function edit(OperationType $type): View
    {
        Gate::authorize('update', $type);

        return view('operations.types.form', [
            'type' => $type,
        ]);
    }

    public function update(OperationTypeRequest $request, OperationType $type): RedirectResponse
    {
        Gate::authorize('update', $type);

        $before = $type->only(['name', 'description', 'cash_multiplier', 'digital_multiplier', 'sort_order']);

        $type->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'cash_multiplier' => $request->input('cash_multiplier'),
            'digital_multiplier' => $request->input('digital_multiplier'),
            'sort_order' => $request->input('sort_order', 0),
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
            'after_values' => $type->only(['name', 'cash_multiplier', 'digital_multiplier']),
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
