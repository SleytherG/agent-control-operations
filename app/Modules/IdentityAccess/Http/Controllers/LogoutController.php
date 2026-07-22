<?php

namespace App\Modules\IdentityAccess\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\IdentityAccess\Application\Actions\RevokeSession;
use App\Modules\IdentityAccess\Domain\Enums\SessionEndReason;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Services\AuthCookieService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __construct(
        private RevokeSession $revokeSession,
        private AuthCookieService $cookieService,
    ) {}

    public function logout(Request $request): RedirectResponse
    {
        $sessionId = $request->get('auth_session_id');

        if ($sessionId) {
            $session = AuthSession::find($sessionId);
            if ($session) {
                $this->revokeSession->execute($session, SessionEndReason::LOGOUT_MANUAL);
            }
        }

        return $this->cookieService->clearAuthCookies(redirect()->route('login'));
    }
}
