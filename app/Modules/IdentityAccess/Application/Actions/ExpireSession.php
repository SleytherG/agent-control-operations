<?php

namespace App\Modules\IdentityAccess\Application\Actions;

use App\Modules\IdentityAccess\Domain\Enums\AuthSessionStatus;
use App\Modules\IdentityAccess\Domain\Enums\SessionEndReason;
use App\Modules\IdentityAccess\Domain\Enums\SessionEventType;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\SessionEvent;

class ExpireSession
{
    public function execute(AuthSession $session): void
    {
        if ($session->status !== AuthSessionStatus::ACTIVE) return;

        $now = now();
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
