<?php

namespace App\Http\Middleware;

use App\Modules\IdentityAccess\Domain\Enums\AuthSessionStatus;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\IdentityAccess\Services\JwtTokenService;
use Closure;
use Illuminate\Http\Request;

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
        $session = AuthSession::where('public_id', $claims['sid'])->first();

        if (! $user || ! $session) {
            return $this->redirectToLogin($request);
        }

        if ($user->status !== UserStatus::ACTIVE || $session->status !== AuthSessionStatus::ACTIVE) {
            return $this->redirectToLogin($request);
        }

        auth()->setUser($user);

        $request->merge(['auth_session_id' => $session->id]);
        $request->attributes->set('session_expires_at', $session->access_expires_at->toIso8601String());

        return $next($request);
    }

    private function redirectToLogin(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return redirect()->route('login');
    }
}
