<?php

namespace App\Modules\IdentityAccess\Application\Actions;

use App\Modules\IdentityAccess\Domain\Enums\AuthSessionStatus;
use App\Modules\IdentityAccess\Domain\Enums\RefreshTokenState;
use App\Modules\IdentityAccess\Domain\Enums\SessionEndReason;
use App\Modules\IdentityAccess\Domain\Enums\SessionEventType;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\AuthRefreshToken;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\SessionEvent;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\IdentityAccess\Services\JwtTokenService;
use App\Modules\IdentityAccess\Services\RefreshTokenService;
use Illuminate\Support\Facades\DB;

class RotateRefreshToken
{
    public function __construct(
        private JwtTokenService $jwtService,
        private RefreshTokenService $refreshService,
    ) {}

    public function execute(string $rawRefreshToken): ?array
    {
        $tokenHash = $this->refreshService->hash($rawRefreshToken);

        return DB::transaction(function () use ($tokenHash) {
            $refreshRow = AuthRefreshToken::where('token_hash', $tokenHash)->lockForUpdate()->first();
            if (! $refreshRow) return null;

            $session = AuthSession::where('id', $refreshRow->auth_session_id)->lockForUpdate()->first();
            if (! $session || $session->status !== AuthSessionStatus::ACTIVE) return null;

            $user = User::find($session->user_id);
            if (! $user || $user->status !== UserStatus::ACTIVE) return null;

            $now = now();

            if ($session->absolute_expires_at <= $now) {
                $this->expireSession($session, $now);
                return null;
            }

            if ($refreshRow->expires_at <= $now) {
                $this->expireSession($session, $now);
                return null;
            }

            if ($refreshRow->state === RefreshTokenState::CONSUMED) {
                $session->update([
                    'status' => AuthSessionStatus::REVOKED,
                    'ended_at' => $now,
                    'end_reason' => SessionEndReason::FALLO_SEGURIDAD->value,
                    'updated_at' => $now,
                ]);
                SessionEvent::create([
                    'auth_session_id' => $session->id,
                    'user_id' => $user->id,
                    'type' => SessionEventType::REFRESH_REUSE->value,
                    'occurred_at' => $now,
                    'created_at' => $now,
                ]);
                return null;
            }

            if ($refreshRow->state !== RefreshTokenState::ACTIVE) return null;

            $ttl = config('session-security.jwt.access_ttl', 300);
            $newRaw = $this->refreshService->generate();
            $newHash = $this->refreshService->hash($newRaw);

            $nextGeneration = $refreshRow->generation + 1;

            $newTokenRow = AuthRefreshToken::create([
                'auth_session_id' => $session->id,
                'token_hash' => $newHash,
                'generation' => $nextGeneration,
                'state' => RefreshTokenState::ACTIVE,
                'issued_at' => $now,
                'expires_at' => $now->copy()->addSeconds($ttl),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $refreshRow->update([
                'state' => RefreshTokenState::CONSUMED,
                'consumed_at' => $now,
                'replaced_by_id' => $newTokenRow->id,
                'updated_at' => $now,
            ]);

            $session->update([
                'access_expires_at' => $now->copy()->addSeconds($ttl),
                'last_refreshed_at' => $now,
                'updated_at' => $now,
            ]);

            SessionEvent::create([
                'auth_session_id' => $session->id,
                'user_id' => $user->id,
                'type' => SessionEventType::REFRESHED->value,
                'occurred_at' => $now,
                'created_at' => $now,
            ]);

            $jwt = $this->jwtService->issue((string) $user->id, $session->public_id);

            return [
                'access_token' => $jwt['token'],
                'refresh_token' => $newRaw,
                'expires_at' => $jwt['expires_at'],
                'ttl' => $ttl,
            ];
        });
    }

    private function expireSession(AuthSession $session, \DateTimeInterface $now): void
    {
        if ($session->status !== AuthSessionStatus::ACTIVE) return;
        $session->update([
            'status' => AuthSessionStatus::EXPIRED,
            'ended_at' => $now,
            'end_reason' => SessionEndReason::EXPIRACION->value,
            'updated_at' => $now,
        ]);
        SessionEvent::create([
            'auth_session_id' => $session->id,
            'user_id' => $session->user_id,
            'type' => SessionEventType::EXPIRED->value,
            'occurred_at' => $now,
            'created_at' => $now,
        ]);
    }
}
