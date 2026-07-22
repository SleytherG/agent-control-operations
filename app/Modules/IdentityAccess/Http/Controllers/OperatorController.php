<?php

namespace App\Modules\IdentityAccess\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Http\Requests\CreateOperatorRequest;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Audit\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OperatorController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $query = User::where('organization_id', auth()->user()->organization_id)
            ->where('role', Role::OPERADOR);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $operators = $query->orderBy('username_normalized')->paginate(20)->withQueryString();

        return view('identity-access.operators.index', compact('operators'));
    }

    public function create(): View
    {
        Gate::authorize('createOperator', User::class);

        return view('identity-access.operators.form', [
            'operator' => new User(),
        ]);
    }

    public function store(CreateOperatorRequest $request): RedirectResponse
    {
        Gate::authorize('createOperator', User::class);

        $operator = User::create([
            'public_id' => (string) Str::uuid(),
            'organization_id' => auth()->user()->organization_id,
            'username_normalized' => Str::lower(trim($request->input('username'))),
            'email_normalized' => Str::lower(trim($request->input('email'))),
            'password' => Hash::make($request->input('password')),
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->logAudit('operator.created', $operator);

        return redirect()->route('admin.users.index')
            ->with('status', 'Operador registrado correctamente.');
    }

    public function edit(User $user): View
    {
        Gate::authorize('updateOperator', $user);

        return view('identity-access.operators.form', [
            'operator' => $user,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('updateOperator', $user);

        $orgId = auth()->user()->organization_id;

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:100', 'unique:users,username_normalized,' . $user->id . ',id,organization_id,' . $orgId],
            'email' => ['required', 'string', 'email', 'max:254', 'unique:users,email_normalized,' . $user->id . ',id,organization_id,' . $orgId],
        ]);

        $before = $user->only(['username_normalized', 'email_normalized']);

        $user->update([
            'username_normalized' => Str::lower(trim($validated['username'])),
            'email_normalized' => Str::lower(trim($validated['email'])),
        ]);

        $this->logAudit('operator.updated', $user, $before);

        return redirect()->route('admin.users.index')
            ->with('status', 'Operador actualizado correctamente.');
    }

    public function deactivate(User $user): RedirectResponse
    {
        Gate::authorize('deactivateOperator', $user);

        $before = $user->only(['status']);
        $user->update([
            'status' => UserStatus::INACTIVE,
            'deactivated_at' => now(),
            'deactivated_by' => auth()->id(),
            'deactivation_reason' => 'Desactivado por administrador',
        ]);

        $this->logAudit('operator.deactivated', $user, $before);

        return redirect()->route('admin.users.index')
            ->with('status', 'Operador desactivado correctamente.');
    }

    private function logAudit(string $action, User $operator, ?array $before = null): void
    {
        AuditLog::create([
            'correlation_id' => (string) \Illuminate\Support\Str::uuid(),
            'created_at' => now(),
            'organization_id' => auth()->user()->organization_id,
            'actor_user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => User::class,
            'entity_id' => $operator->id,
            'before_values' => $before,
            'after_values' => $operator->only(['username_normalized', 'email_normalized', 'role', 'status']),
            'occurred_at' => now(),
        ]);
    }
}
