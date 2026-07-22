<?php

namespace App\Modules\BankingNetwork\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BankingNetwork\Http\Requests\BankRequest;
use App\Modules\BankingNetwork\Models\Bank;
use App\Modules\Audit\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BankController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Bank::class);

        $banks = Bank::where('organization_id', auth()->user()->organization_id)
            ->orderBy('name')
            ->paginate(20)->withQueryString();

        return view('banking-network.banks.index', compact('banks'));
    }

    public function create(): View
    {
        Gate::authorize('create', Bank::class);

        return view('banking-network.banks.form', [
            'bank' => new Bank(),
        ]);
    }

    public function store(BankRequest $request): RedirectResponse
    {
        Gate::authorize('create', Bank::class);

        $bank = Bank::create([
            'organization_id' => auth()->user()->organization_id,
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'is_active' => true,
        ]);

        $this->logAudit('bank.created', $bank);

        return redirect()->route('admin.banks.index')
            ->with('status', 'Banco creado correctamente.');
    }

    public function update(BankRequest $request, Bank $bank): RedirectResponse
    {
        Gate::authorize('update', $bank);

        $before = $bank->only(['code', 'name']);

        $bank->update([
            'code' => $request->input('code'),
            'name' => $request->input('name'),
        ]);

        $this->logAudit('bank.updated', $bank, $before);

        return redirect()->route('admin.banks.index')
            ->with('status', 'Banco actualizado correctamente.');
    }

    public function deactivate(Bank $bank): RedirectResponse
    {
        Gate::authorize('deactivate', $bank);

        $before = $bank->only(['is_active']);
        $bank->update(['is_active' => false, 'deactivated_at' => now()]);

        $this->logAudit('bank.deactivated', $bank, $before);

        return redirect()->route('admin.banks.index')
            ->with('status', 'Banco desactivado correctamente.');
    }

    private function logAudit(string $action, Bank $bank, ?array $before = null): void
    {
        AuditLog::create([
            'correlation_id' => (string) \Illuminate\Support\Str::uuid(),
            'created_at' => now(),
            'organization_id' => auth()->user()->organization_id,
            'actor_user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => Bank::class,
            'entity_id' => $bank->id,
            'before_values' => $before,
            'after_values' => $bank->only(['code', 'name', 'is_active']),
            'occurred_at' => now(),
        ]);
    }
}
