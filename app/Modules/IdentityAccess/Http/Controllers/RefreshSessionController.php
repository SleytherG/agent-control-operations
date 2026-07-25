<?php

namespace App\Modules\IdentityAccess\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\IdentityAccess\Application\Actions\RotateRefreshToken;
use App\Modules\IdentityAccess\Services\AuthCookieService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RefreshSessionController extends Controller
{
    public function __construct(
        private RotateRefreshToken $rotateRefreshToken,
        private AuthCookieService $cookieService,
    ) {}

    public function refresh(Request $request): JsonResponse
    {
        $rawToken = $request->cookie(config('session-security.cookies.refresh_name'));

        if (! $rawToken) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $result = $this->rotateRefreshToken->execute($rawToken);

        if (! $result) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $response = response()->json([
            'expiresAt' => $result['expires_at']->format(\DateTimeInterface::ATOM),
        ]);

        return $this->cookieService->withAuthCookies(
            $response,
            $result['access_token'],
            $result['refresh_token'],
            $result['ttl'],
        );
    }
}
