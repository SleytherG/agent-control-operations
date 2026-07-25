<?php

namespace App\Modules\IdentityAccess\Application\Actions;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\SessionEndReason;
use App\Modules\IdentityAccess\Domain\Enums\SessionEventType;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Audit\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class DeactivateUser
{
    public function __construct(private RevokeAllUserSessions $revokeAllUserSessions) {}

    public function execute(User $target, User $actor, string $reason): void
    {
        if ($target->id === $actor->id) {
            throw new RuntimeException('No puede desactivarse a sí mismo.');
        }

        DB::transaction(function () use ($target, $actor, $reason) {
            $user = User::where('id', $target->id)->lockForUpdate()->first();
            if (! $user || $user->status !== UserStatus::ACTIVE) {
                return;
            }

            if ($user->role === Role::ADMINISTRADOR_PROPIETARIO) {
                $remainingAdmins = User::where('organization_id', $user->organization_id)
                    ->where('role', Role::ADMINISTRADOR_PROPIETARIO)
                    ->where('status', UserStatus::ACTIVE)
                    ->where('id', '!=', $user->id)
                    ->count();

                if ($remainingAdmins === 0) {
                    throw new RuntimeException(
                        'No puede desactivar al último administrador propietario activo.'
                    );
                }
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
                'correlation_id' => (string) Str::uuid(),
                'created_at' => $now,
                'organization_id' => $user->organization_id,
                'actor_user_id' => $actor->id,
                'action' => 'USER_DEACTIVATED',
                'entity_type' => 'User',
                'entity_id' => $user->id,
                'before_values' => $before,
                'after_values' => $after,
                'reason' => $reason,
                'occurred_at' => $now,
            ]);

            $this->revokeAllUserSessions->execute(
                $user,
                SessionEndReason::REVOCACION_ADMINISTRATIVA,
                SessionEventType::ADMIN_REVOKED,
            );
        });
    }
}
