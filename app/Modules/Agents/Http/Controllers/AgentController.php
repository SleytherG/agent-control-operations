<?php

namespace App\Modules\Agents\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Agents\Http\Requests\AgentRequest;
use App\Modules\Agents\Models\Agent;
use App\Modules\Audit\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AgentController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Agent::class);

        $orgId = auth()->user()->organization_id;

        $query = Agent::where('organization_id', $orgId);

        if ($request->filled('code')) {
            $query->where('code', 'like', '%' . $request->input('code') . '%');
        }

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->input('city') . '%');
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $agents = $query->orderBy('code')->paginate(20)->withQueryString();
        $cities = Agent::where('organization_id', $orgId)->distinct()->pluck('city')->filter()->values();

        return view('agents.index', compact('agents', 'cities'));
    }

    public function create(): View
    {
        Gate::authorize('create', Agent::class);

        $cities = Agent::where('organization_id', auth()->user()->organization_id)
            ->distinct()->pluck('city')->filter()->values();

        return view('agents.form', [
            'agent' => new Agent(),
            'cities' => $cities,
        ]);
    }

    public function store(AgentRequest $request): RedirectResponse
    {
        Gate::authorize('create', Agent::class);

        $agent = Agent::create([
            'organization_id' => auth()->user()->organization_id,
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'city' => $request->input('city'),
            'region' => $request->input('region'),
            'province' => $request->input('province'),
            'district' => $request->input('district'),
            'address' => $request->input('address'),
            'description' => $request->input('description'),
            'is_active' => true,
        ]);

        $this->logAudit('agent.created', $agent);

        return redirect()->route('admin.agents.index')
            ->with('status', 'Agente creado correctamente.');
    }

    public function edit(Agent $agent): View
    {
        Gate::authorize('update', $agent);

        $cities = Agent::where('organization_id', auth()->user()->organization_id)
            ->distinct()->pluck('city')->filter()->values();

        return view('agents.form', compact('agent', 'cities'));
    }

    public function update(AgentRequest $request, Agent $agent): RedirectResponse
    {
        Gate::authorize('update', $agent);

        $before = $agent->only(['code', 'name', 'city', 'region', 'province', 'district', 'address', 'description']);

        $agent->update([
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'city' => $request->input('city'),
            'region' => $request->input('region'),
            'province' => $request->input('province'),
            'district' => $request->input('district'),
            'address' => $request->input('address'),
            'description' => $request->input('description'),
        ]);

        $this->logAudit('agent.updated', $agent, $before);

        return redirect()->route('admin.agents.index')
            ->with('status', 'Agente actualizado correctamente.');
    }

    public function deactivate(Agent $agent): RedirectResponse
    {
        Gate::authorize('deactivate', $agent);

        if (! $agent->is_active) {
            return redirect()->route('admin.agents.index')
                ->with('status', 'El agente ya está inactivo.');
        }

        DB::transaction(function () use ($agent) {
            $agent->activeAssignments()->update([
                'is_active' => false,
                'ends_at' => now(),
            ]);

            $before = $agent->only(['is_active']);
            $agent->update([
                'is_active' => false,
                'deactivated_at' => now(),
            ]);

            $this->logAudit('agent.deactivated', $agent, $before);
        });

        return redirect()->route('admin.agents.index')
            ->with('status', 'Agente desactivado y asignaciones terminadas correctamente.');
    }

    private function logAudit(string $action, Agent $agent, ?array $before = null): void
    {
        AuditLog::create([
            'correlation_id' => (string) Str::uuid(),
            'created_at' => now(),
            'organization_id' => auth()->user()->organization_id,
            'actor_user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => Agent::class,
            'entity_id' => $agent->id,
            'before_values' => $before,
            'after_values' => $agent->only(['code', 'name', 'city', 'is_active']),
            'occurred_at' => now(),
        ]);
    }
}
