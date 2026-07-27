<?php

namespace App\Modules\IdentityAccess\Application\Actions;

use App\Modules\IdentityAccess\Domain\Enums\AuthSessionStatus;
use App\Modules\IdentityAccess\Domain\Enums\RefreshTokenState;
use App\Modules\IdentityAccess\Domain\Enums\SessionEventType;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\AuthRefreshToken;
use App\Modules\IdentityAccess\Models\SessionEvent;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\IdentityAccess\Models\PasswordReset;
use App\Modules\IdentityAccess\Services\JwtTokenService;
use App\Modules\IdentityAccess\Services\RefreshTokenService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StartAuthSession
{
    public function __construct(
        private JwtTokenService $jwtService,
        private RefreshTokenService $refreshService,
    ) {}

    public function execute(
        User $user,
        ?string $ipHash = null,
        ?string $userAgent = null,
        ?PasswordReset $passwordReset = null,
        SessionEventType $eventType = SessionEventType::LOGIN,
    ): array
    {
        $now = now();
        $ttl = config('session-security.jwt.access_ttl', 300);
        $absoluteTtl = config('session-security.session.absolute_ttl', 28800);

        return DB::transaction(function () use (
            $user,
            $now,
            $ttl,
            $absoluteTtl,
            $ipHash,
            $userAgent,
            $passwordReset,
            $eventType,
        ) {
            $session = AuthSession::create([
                'public_id' => (string) Str::uuid(),
                'user_id' => $user->id,
                'password_reset_id' => $passwordReset?->id,
                'status' => AuthSessionStatus::ACTIVE,
                'started_at' => $now,
                'access_expires_at' => $now->copy()->addSeconds($ttl),
                'absolute_expires_at' => $now->copy()->addSeconds($absoluteTtl),
                'ip_hash' => $ipHash ? hash('sha256', $ipHash) : null,
                'user_agent_summary' => mb_substr($userAgent ?? '', 0, 255),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $refreshToken = $this->refreshService->generate();
            $tokenHash = $this->refreshService->hash($refreshToken);

            AuthRefreshToken::create([
                'auth_session_id' => $session->id,
                'token_hash' => $tokenHash,
                'generation' => 1,
                'state' => RefreshTokenState::ACTIVE,
                'issued_at' => $now,
                'expires_at' => $now->copy()->addSeconds($ttl),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            SessionEvent::create([
                'auth_session_id' => $session->id,
                'user_id' => $user->id,
                'type' => $eventType->value,
                'occurred_at' => $now,
                'created_at' => $now,
            ]);

            $jwt = $this->jwtService->issue((string) $user->id, $session->public_id);

            return [
                'access_token' => $jwt['token'],
                'refresh_token' => $refreshToken,
                'expires_at' => $jwt['expires_at'],
                'ttl' => $ttl,
                'session' => $session,
            ];
        });
    }
}
