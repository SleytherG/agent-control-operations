<?php

namespace App\Modules\Agents\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Agents\Http\Requests\AssignAgentRequest;
use App\Modules\Agents\Models\Agent;
use App\Modules\Agents\Models\UserAgentAssignment;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Audit\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserAgentAssignmentController extends Controller
{
    public function index(Request $request, User $user): View
    {
        Gate::authorize('viewAny', Agent::class);

        $assignments = UserAgentAssignment::with('agent')
            ->where('user_id', $user->id)
            ->orderBy('starts_at', 'desc')
            ->paginate(20)->withQueryString();

        $availableAgents = Agent::where('organization_id', auth()->user()->organization_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('agents.assignments._modal', compact('assignments', 'user', 'availableAgents'));
        }

        return view('agents.assignments.index', compact('assignments', 'user', 'availableAgents'));
    }

    public function store(AssignAgentRequest $request, User $user): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        Gate::authorize('viewAny', Agent::class);

        $actor = auth()->user();
        $agentId = $request->input('agent_id');

        $existing = UserAgentAssignment::where('user_id', $user->id)
            ->where('agent_id', $agentId)
            ->where('is_active', true)
            ->exists();

        if ($existing) {
            if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['message' => 'El agente seleccionado ya está asignado a este operador.'], 409);
            }
            return redirect()->back()
                ->with('status', 'El agente seleccionado ya está asignado a este operador.')
                ->withInput();
        }

        $assignment = UserAgentAssignment::create([
            'user_id' => $user->id,
            'agent_id' => $agentId,
            'assigned_by' => $actor->id,
            'starts_at' => now(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        AuditLog::create([
            'correlation_id' => (string) Str::uuid(),
            'created_at' => now(),
            'organization_id' => $actor->organization_id,
            'actor_user_id' => $actor->id,
            'action' => 'assignment.created',
            'entity_type' => UserAgentAssignment::class,
            'entity_id' => $assignment->id,
            'after_values' => ['user_id' => $user->id, 'agent_id' => $agentId],
            'occurred_at' => now(),
        ]);

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['message' => 'Operador asignado correctamente.']);
        }

        return redirect()->route('admin.users.assignments.index', $user)
            ->with('status', 'Operador asignado correctamente.');
    }

    public function destroy(Request $request, UserAgentAssignment $assignment): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        Gate::authorize('viewAny', Agent::class);

        if (! $assignment->is_active) {
            if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['message' => 'La asignación ya está inactiva.'], 409);
            }
            return redirect()->back()->with('status', 'La asignación ya está inactiva.');
        }

        $before = $assignment->only(['is_active', 'ends_at']);

        $assignment->update([
            'is_active' => false,
            'ends_at' => now(),
        ]);

        $actor = auth()->user();

        AuditLog::create([
            'correlation_id' => (string) Str::uuid(),
            'created_at' => now(),
            'organization_id' => $actor->organization_id,
            'actor_user_id' => $actor->id,
            'action' => 'assignment.deactivated',
            'entity_type' => UserAgentAssignment::class,
            'entity_id' => $assignment->id,
            'before_values' => $before,
            'after_values' => $assignment->only(['is_active', 'ends_at']),
            'occurred_at' => now(),
        ]);

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['message' => 'Operador desasignado correctamente.']);
        }

        return redirect()->back()
            ->with('status', 'Operador desasignado correctamente.');
    }
}
