<?php

namespace App\Modules\BankingNetwork\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BankingNetwork\Http\Requests\BankAgentRequest;
use App\Modules\BankingNetwork\Models\BankAgent;
use App\Modules\BankingNetwork\Models\Bank;
use App\Modules\Organization\Models\Store;
use App\Modules\Audit\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BankAgentController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', BankAgent::class);

        $orgId = auth()->user()->organization_id;

        $query = BankAgent::with(['store', 'bank'])
            ->where('organization_id', $orgId);

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->input('store_id'));
        }

        if ($request->filled('bank_id')) {
            $query->where('bank_id', $request->input('bank_id'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $agents = $query->orderBy('code')->paginate(20)->withQueryString();
        $stores = Store::where('organization_id', $orgId)->where('is_active', true)->orderBy('name')->get();
        $banks = Bank::where('organization_id', $orgId)->where('is_active', true)->orderBy('name')->get();

        return view('banking-network.agents.index', compact('agents', 'stores', 'banks'));
    }

    public function create(): View
    {
        Gate::authorize('create', BankAgent::class);

        $orgId = auth()->user()->organization_id;

        return view('banking-network.agents.form', [
            'agent' => new BankAgent(),
            'stores' => Store::where('organization_id', $orgId)->where('is_active', true)->orderBy('name')->get(),
            'banks' => Bank::where('organization_id', $orgId)->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(BankAgentRequest $request): RedirectResponse
    {
        Gate::authorize('create', BankAgent::class);

        $agent = BankAgent::create([
            'organization_id' => auth()->user()->organization_id,
            'store_id' => $request->input('store_id'),
            'bank_id' => $request->input('bank_id'),
            'code' => $request->input('code'),
            'terminal_code' => $request->input('terminal_code'),
            'is_active' => true,
        ]);

        $this->logAudit('bank_agent.created', $agent);

        return redirect()->route('admin.bank-agents.index')
            ->with('status', 'Agente bancario creado correctamente.');
    }

    public function update(BankAgentRequest $request, BankAgent $agent): RedirectResponse
    {
        Gate::authorize('update', $agent);

        $before = $agent->only(['store_id', 'bank_id', 'code', 'terminal_code']);

        $agent->update([
            'store_id' => $request->input('store_id'),
            'bank_id' => $request->input('bank_id'),
            'code' => $request->input('code'),
            'terminal_code' => $request->input('terminal_code'),
        ]);

        $this->logAudit('bank_agent.updated', $agent, $before);

        return redirect()->route('admin.bank-agents.index')
            ->with('status', 'Agente bancario actualizado correctamente.');
    }

    public function deactivate(BankAgent $agent): RedirectResponse
    {
        Gate::authorize('deactivate', $agent);

        if (! $agent->is_active) {
            return redirect()->route('admin.bank-agents.index')
                ->with('status', 'El agente ya está inactivo.');
        }

        DB::transaction(function () use ($agent) {
            $agent->activeAssignments()->update([
                'is_active' => false,
                'unassigned_at' => now(),
            ]);

            $before = $agent->only(['is_active']);
            $agent->update([
                'is_active' => false,
                'deactivated_at' => now(),
            ]);

            $this->logAudit('bank_agent.deactivated', $agent, $before);
        });

        return redirect()->route('admin.bank-agents.index')
            ->with('status', 'Agente desactivado y asignaciones terminadas correctamente.');
    }

    private function logAudit(string $action, BankAgent $agent, ?array $before = null): void
    {
        AuditLog::create([
            'correlation_id' => (string) \Illuminate\Support\Str::uuid(),
            'created_at' => now(),
            'organization_id' => auth()->user()->organization_id,
            'actor_user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => BankAgent::class,
            'entity_id' => $agent->id,
            'before_values' => $before,
            'after_values' => $agent->only(['store_id', 'bank_id', 'code', 'terminal_code', 'is_active']),
            'occurred_at' => now(),
        ]);
    }
}
