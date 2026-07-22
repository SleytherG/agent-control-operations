<?php

namespace App\Modules\IdentityAccess\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\IdentityAccess\Application\Actions\AuthenticateUser;
use App\Modules\IdentityAccess\Application\Actions\StartAuthSession;
use App\Modules\IdentityAccess\Http\Requests\LoginRequest;
use App\Modules\IdentityAccess\Services\AuthCookieService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        return view('identity-access.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $user = $this->authenticateUser->execute(
            $request->normalizedIdentifier(),
            $request->input('password'),
        );

        if (! $user) {
            return redirect()->route('login')
                ->withErrors(['identifier' => 'Credenciales inválidas.'])
                ->withInput($request->only('identifier'));
        }

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

    public function home(Request $request): View
    {
        return view('identity-access.home', [
            'expiresAt' => $request->attributes->get('session_expires_at'),
        ]);
    }
}
