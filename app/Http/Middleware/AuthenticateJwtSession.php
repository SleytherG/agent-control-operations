<?php

namespace App\Http\Middleware;

use App\Modules\IdentityAccess\Domain\Enums\AuthSessionStatus;
use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\PasswordResetStatus;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\IdentityAccess\Services\JwtTokenService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class AuthenticateJwtSession
{
    public function handle(Request $request, Closure $next)
    {
        $jwt = $request->cookie(config('session-security.cookies.access_name'));

        if (! $jwt) {
            return $this->redirectToLogin($request);
        }

        $jwtService = app(JwtTokenService::class);
        $claims = $jwtService->validate($jwt);

        if (! $claims) {
            return $this->redirectToLogin($request);
        }

        $user = User::find($claims['sub']);
        $session = AuthSession::with('passwordReset')->where('public_id', $claims['sid'])->first();

        if (! $user || ! $session) {
            return $this->redirectToLogin($request);
        }

        if ($user->status !== UserStatus::ACTIVE || $session->status !== AuthSessionStatus::ACTIVE) {
            return $this->redirectToLogin($request);
        }

        auth()->setUser($user);

        View::share('user', $user);
        View::share('role', $user->role === Role::ADMINISTRADOR_PROPIETARIO ? 'admin' : 'operator');
        View::share('sessionExpiresAt', $session->access_expires_at);

        $request->merge(['auth_session_id' => $session->id]);
        $request->attributes->set('auth_session', $session);
        $request->attributes->set('session_expires_at', $session->access_expires_at->toIso8601String());

        $restrictedReset = $session->passwordReset?->status === PasswordResetStatus::CONSUMED;
        $mustChangeInitialPassword = is_null($user->password_changed_at) && ! $session->password_reset_id;
        $allowedRoute = in_array($request->route()?->getName(), [
            'password.change',
            'password.change.update',
            'logout',
        ], true);

        if (($restrictedReset || $mustChangeInitialPassword) && ! $allowedRoute) {
            if ($request->expectsJson() || ! $request->isMethod('GET')) {
                return response()->json(['message' => 'Debe cambiar su contraseña antes de continuar.'], 403);
            }

            return redirect()->route('password.change');
        }

        return $next($request);
    }

    private function redirectToLogin(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (! $request->isMethod('GET')) {
            $request->session()->flashInput($request->input());
            $request->session()->flash('session_expired', true);
        }

        return redirect()->route('login')->with('session_expired', true);
    }
}
