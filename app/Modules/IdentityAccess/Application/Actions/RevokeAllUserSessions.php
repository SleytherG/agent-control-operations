<?php

namespace App\Modules\IdentityAccess\Application\Actions;

use App\Modules\IdentityAccess\Domain\Enums\AuthSessionStatus;
use App\Modules\IdentityAccess\Domain\Enums\RefreshTokenState;
use App\Modules\IdentityAccess\Domain\Enums\SessionEndReason;
use App\Modules\IdentityAccess\Domain\Enums\SessionEventType;
use App\Modules\IdentityAccess\Models\AuthRefreshToken;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\SessionEvent;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Support\Facades\DB;

class RevokeAllUserSessions
{
    public function execute(
        User $user,
        SessionEndReason $reason,
        SessionEventType $eventType,
    ): int {
        return DB::transaction(function () use ($user, $reason, $eventType) {
            $now = now();
            $sessions = AuthSession::query()
                ->where('user_id', $user->id)
                ->where('status', AuthSessionStatus::ACTIVE)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($sessions as $session) {
                $session->update([
                    'status' => AuthSessionStatus::REVOKED,
                    'ended_at' => $now,
                    'end_reason' => $reason,
                ]);

                AuthRefreshToken::query()
                    ->where('auth_session_id', $session->id)
                    ->where('state', RefreshTokenState::ACTIVE)
                    ->update([
                        'state' => RefreshTokenState::REVOKED,
                        'revoked_at' => $now,
                        'updated_at' => $now,
                    ]);

                SessionEvent::create([
                    'auth_session_id' => $session->id,
                    'user_id' => $user->id,
                    'type' => $eventType->value,
                    'occurred_at' => $now,
                    'context' => ['reason' => $reason->value],
                    'created_at' => $now,
                ]);
            }

            return $sessions->count();
        });
    }
}
