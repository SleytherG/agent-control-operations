<?php

namespace App\Modules\Organization\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Organization\Http\Requests\StoreRequest;
use App\Modules\Organization\Models\Store;
use App\Modules\Organization\Models\District;
use App\Modules\Audit\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Store::class);

        $query = Store::with('district.province.region')
            ->where('organization_id', auth()->user()->organization_id);

        if ($request->filled('district_id')) {
            $query->where('district_id', $request->input('district_id'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $stores = $query->orderBy('name')->paginate(20)->withQueryString();
        $districts = District::where('organization_id', auth()->user()->organization_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('organization.stores.index', compact('stores', 'districts'));
    }

    public function create(): View
    {
        Gate::authorize('create', Store::class);

        $districts = District::with('province.region')
            ->where('organization_id', auth()->user()->organization_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('organization.stores.form', [
            'store' => new Store(),
            'districts' => $districts,
        ]);
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        Gate::authorize('create', Store::class);

        $store = Store::create([
            'organization_id' => auth()->user()->organization_id,
            'district_id' => $request->input('district_id'),
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'address' => $request->input('address'),
            'is_active' => true,
        ]);

        $this->logAudit('store.created', $store);

        return redirect()->route('admin.stores.index')
            ->with('status', 'Tienda creada correctamente.');
    }

    public function show(Store $store): View
    {
        Gate::authorize('view', $store);

        $store->load(['district.province.region', 'activeBankAgents.bank']);

        return view('organization.stores.form', [
            'store' => $store,
            'districts' => District::where('organization_id', auth()->user()->organization_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'readonly' => true,
        ]);
    }

    public function update(StoreRequest $request, Store $store): RedirectResponse
    {
        Gate::authorize('update', $store);

        $before = $store->only(['code', 'name', 'address', 'district_id']);

        $store->update([
            'district_id' => $request->input('district_id'),
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'address' => $request->input('address'),
        ]);

        $this->logAudit('store.updated', $store, $before);

        return redirect()->route('admin.stores.index')
            ->with('status', 'Tienda actualizada correctamente.');
    }

    public function deactivate(Store $store): RedirectResponse
    {
        Gate::authorize('deactivate', $store);

        if ($store->hasActiveAgents()) {
            return redirect()->route('admin.stores.index')
                ->withErrors(['deactivate' => 'No se puede desactivar una tienda con agentes activos.']);
        }

        $before = $store->only(['is_active']);
        $store->update([
            'is_active' => false,
            'deactivated_at' => now(),
        ]);

        $this->logAudit('store.deactivated', $store, $before);

        return redirect()->route('admin.stores.index')
            ->with('status', 'Tienda desactivada correctamente.');
    }

    private function logAudit(string $action, Store $store, ?array $before = null): void
    {
        AuditLog::create([
            'correlation_id' => (string) Str::uuid(),
            'created_at' => now(),
            'organization_id' => auth()->user()->organization_id,
            'actor_user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => Store::class,
            'entity_id' => $store->id,
            'before_values' => $before,
            'after_values' => $store->only(['code', 'name', 'address', 'district_id', 'is_active']),
            'occurred_at' => now(),
        ]);
    }
}
