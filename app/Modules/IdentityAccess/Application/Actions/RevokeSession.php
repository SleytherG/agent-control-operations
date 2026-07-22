<?php

namespace App\Modules\IdentityAccess\Application\Actions;

use App\Modules\IdentityAccess\Domain\Enums\AuthSessionStatus;
use App\Modules\IdentityAccess\Domain\Enums\SessionEndReason;
use App\Modules\IdentityAccess\Domain\Enums\SessionEventType;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\SessionEvent;
use Illuminate\Support\Facades\DB;

class RevokeSession
{
    public function execute(AuthSession $session, SessionEndReason $reason): void
    {
        DB::transaction(function () use ($session, $reason) {
            $fresh = AuthSession::where('id', $session->id)->lockForUpdate()->first();
            if (! $fresh || $fresh->status !== AuthSessionStatus::ACTIVE) return;

            $now = now();
            $fresh->update([
                'status' => AuthSessionStatus::REVOKED,
                'ended_at' => $now,
                'end_reason' => $reason->value,
                'updated_at' => $now,
            ]);

            SessionEvent::create([
                'auth_session_id' => $fresh->id,
                'user_id' => $fresh->user_id,
                'type' => $reason === SessionEndReason::LOGOUT_MANUAL
                    ? SessionEventType::LOGOUT->value
                    : SessionEventType::ADMIN_REVOKED->value,
                'occurred_at' => $now,
                'created_at' => $now,
            ]);
        });
    }
}
