<?php

namespace App\Modules\IdentityAccess\Application\Actions;

use App\Modules\IdentityAccess\Models\SessionEvent;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\User;

class RecordSessionEvent
{
    public function execute(AuthSession $session, User $user, string $type, ?array $context = null): SessionEvent
    {
        $now = now();

        return SessionEvent::create([
            'auth_session_id' => $session->id,
            'user_id' => $user->id,
            'type' => $type,
            'occurred_at' => $now,
            'context' => $context,
            'created_at' => $now,
        ]);
    }
}
