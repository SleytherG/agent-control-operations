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
use App\Modules\Audit\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class DeactivateUser
{
    public function execute(User $target, User $actor, string $reason): void
    {
        DB::transaction(function () use ($target, $actor, $reason) {
            $user = User::where('id', $target->id)->lockForUpdate()->first();
            if (! $user || $user->status !== UserStatus::ACTIVE) {
                return;
            }

            $now = now();

            $before = [
                'status' => $user->status->value,
                'role' => $user->role->value,
            ];

            $user->update([
                'status' => UserStatus::INACTIVE,
                'deactivated_at' => $now,
                'deactivated_by' => $actor->id,
                'deactivation_reason' => $reason,
                'updated_at' => $now,
            ]);

            $after = [
                'status' => $user->status->value,
                'role' => $user->role->value,
            ];

            AuditLog::create([
                'organization_id' => $user->organization_id,
                'actor_user_id' => $actor->id,
                'action' => 'USER_DEACTIVATED',
                'entity_type' => 'User',
                'entity_id' => $user->id,
                'before_values' => $before,
                'after_values' => $after,
                'reason' => $reason,
                'occurred_at' => $now,
                'created_at' => $now,
            ]);

            $activeSessions = AuthSession::where('user_id', $user->id)
                ->where('status', AuthSessionStatus::ACTIVE)
                ->get();

            foreach ($activeSessions as $session) {
                $fresh = AuthSession::where('id', $session->id)->lockForUpdate()->first();
                if (! $fresh || $fresh->status !== AuthSessionStatus::ACTIVE) {
                    continue;
                }

                $fresh->update([
                    'status' => AuthSessionStatus::REVOKED,
                    'ended_at' => $now,
                    'end_reason' => SessionEndReason::REVOCACION_ADMINISTRATIVA->value,
                    'updated_at' => $now,
                ]);

                AuthRefreshToken::where('auth_session_id', $fresh->id)
                    ->where('state', RefreshTokenState::ACTIVE)
                    ->update([
                        'state' => RefreshTokenState::REVOKED,
                        'revoked_at' => $now,
                        'updated_at' => $now,
                    ]);

                SessionEvent::create([
                    'auth_session_id' => $fresh->id,
                    'user_id' => $user->id,
                    'type' => SessionEventType::ADMIN_REVOKED->value,
                    'occurred_at' => $now,
                    'created_at' => $now,
                ]);
            }
        });
    }
}
