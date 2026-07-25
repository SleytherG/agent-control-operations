<?php

namespace App\Modules\IdentityAccess\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\IdentityAccess\Application\Actions\CompleteRequiredPasswordChange;
use App\Modules\IdentityAccess\Http\Requests\CompletePasswordChangeRequest;
use App\Modules\IdentityAccess\Models\AuthSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PasswordChangeController extends Controller
{
    public function show(Request $request): View
    {
        $session = AuthSession::find($request->input('auth_session_id'));

        return view('identity-access.password-change', [
            'restrictedReset' => (bool) $session?->password_reset_id,
        ]);
    }

    public function update(
        CompletePasswordChangeRequest $request,
        CompleteRequiredPasswordChange $completeRequiredPasswordChange,
    ): RedirectResponse {
        $session = AuthSession::findOrFail($request->input('auth_session_id'));
        $user = $request->user();

        if ($session->password_reset_id) {
            $completeRequiredPasswordChange->execute(
                $session->id,
                $user,
                $request->string('password')->toString(),
                $request->header('X-Correlation-ID'),
            );

            return redirect()->route('dashboard.operator')
                ->with('status', 'Contraseña actualizada correctamente.');
        }

        if (! Hash::check($request->string('current_password')->toString(), $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'La contraseña actual no es correcta.']);
        }

        $user->update([
            'password' => Hash::make($request->string('password')->toString()),
            'password_changed_at' => now(),
        ]);

        $redirectRoute = $user->isAdministradorPropietario() ? 'admin.dashboard' : 'home';

        return redirect()->route($redirectRoute)
            ->with('status', 'Contraseña actualizada correctamente.');
    }
}
