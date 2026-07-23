<?php

namespace App\Modules\IdentityAccess\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\IdentityAccess\Application\Actions\AuthenticateUser;
use App\Modules\IdentityAccess\Application\Actions\StartAuthSession;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Http\Requests\LoginRequest;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\IdentityAccess\Services\AuthCookieService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(
        private AuthenticateUser $authenticateUser,
        private StartAuthSession $startAuthSession,
        private AuthCookieService $cookieService,
    ) {}

    public function showLoginForm(Request $request): View
    {
        $loginState = session('login_state', 'normal');

        return view('identity-access.login', [
            'title' => 'AgenteFlow - Iniciar Sesion',
        ]);
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $key = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($key, config('session-security.throttle.max_attempts', 5))) {
            $seconds = RateLimiter::availableIn($key);

            return redirect()->route('login')
                ->with('login_state', 'throttled')
                ->withInput($request->only('identifier'));
        }

        $normalizedIdentifier = $request->normalizedIdentifier();
        $password = $request->input('password');

        $existingUser = User::where('username_normalized', $normalizedIdentifier)
            ->orWhere('email_normalized', $normalizedIdentifier)
            ->first();

        if ($existingUser && $existingUser->status !== UserStatus::ACTIVE) {
            return redirect()->route('login')
                ->with('login_state', 'disabled')
                ->withInput($request->only('identifier'));
        }

        $user = $this->authenticateUser->execute($normalizedIdentifier, $password);

        if (! $user) {
            RateLimiter::hit($key, config('session-security.throttle.decay_seconds', 60));

            return redirect()->route('login')
                ->with('login_state', 'error')
                ->withInput($request->only('identifier'));
        }

        RateLimiter::clear($key);

        $result = $this->startAuthSession->execute(
            $user,
            $request->ip(),
            $request->userAgent(),
        );

        $response = redirect()->route('home');

        return $this->cookieService->withAuthCookies(
            $response,
            $result['access_token'],
            $result['refresh_token'],
            $result['ttl'],
        );
    }

    private function throttleKey(LoginRequest $request): string
    {
        $identifier = Str::lower(trim($request->input('identifier', '')));
        $ipHash = substr(hash('sha256', $request->ip()), 0, 12);

        return "login:{$identifier}:{$ipHash}";
    }

    public function home(Request $request): View
    {
        return view('identity-access.home', [
            'title' => 'AgenteFlow - Inicio',
            'expiresAt' => $request->attributes->get('session_expires_at'),
        ]);
    }
}
