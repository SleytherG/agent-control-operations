<?php

namespace App\Modules\BankingNetwork\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BankingNetwork\Http\Requests\AssignOperatorRequest;
use App\Modules\BankingNetwork\Models\UserBankAgentAssignment;
use App\Modules\BankingNetwork\Models\BankAgent;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Audit\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class UserBankAgentAssignmentController extends Controller
{
    public function index(Request $request, User $user): View
    {
        Gate::authorize('viewAny', BankAgent::class);

        $assignments = UserBankAgentAssignment::with(['bankAgent.store', 'bankAgent.bank'])
            ->where('user_id', $user->id)
            ->orderBy('assigned_at', 'desc')
            ->paginate(20)->withQueryString();

        return view('banking-network.assignments.index', compact('assignments', 'user'));
    }

    public function store(AssignOperatorRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('viewAny', BankAgent::class);

        $actor = auth()->user();

        $assignment = UserBankAgentAssignment::create([
            'user_id' => $user->id,
            'bank_agent_id' => $request->input('bank_agent_id'),
            'assigned_by' => $actor->id,
            'assigned_at' => now(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        AuditLog::create([
            'correlation_id' => (string) \Illuminate\Support\Str::uuid(),
            'created_at' => now(),
            'organization_id' => $actor->organization_id,
            'actor_user_id' => $actor->id,
            'action' => 'assignment.created',
            'entity_type' => UserBankAgentAssignment::class,
            'entity_id' => $assignment->id,
            'after_values' => ['user_id' => $user->id, 'bank_agent_id' => $request->input('bank_agent_id')],
            'occurred_at' => now(),
        ]);

        return redirect()->route('admin.users.assignments.index', $user)
            ->with('status', 'Operador asignado correctamente.');
    }

    public function destroy(UserBankAgentAssignment $assignment): RedirectResponse
    {
        Gate::authorize('viewAny', BankAgent::class);

        if (! $assignment->is_active) {
            return redirect()->back()->with('status', 'La asignación ya está inactiva.');
        }

        $before = $assignment->only(['is_active', 'unassigned_at']);

        $assignment->update([
            'is_active' => false,
            'unassigned_at' => now(),
        ]);

        $actor = auth()->user();

        AuditLog::create([
            'correlation_id' => (string) \Illuminate\Support\Str::uuid(),
            'created_at' => now(),
            'organization_id' => $actor->organization_id,
            'actor_user_id' => $actor->id,
            'action' => 'assignment.deactivated',
            'entity_type' => UserBankAgentAssignment::class,
            'entity_id' => $assignment->id,
            'before_values' => $before,
            'after_values' => $assignment->only(['is_active', 'unassigned_at']),
            'occurred_at' => now(),
        ]);

        return redirect()->back()
            ->with('status', 'Operador desasignado correctamente.');
    }
}
