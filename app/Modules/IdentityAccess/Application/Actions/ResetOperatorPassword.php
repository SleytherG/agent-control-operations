<?php

namespace App\Modules\IdentityAccess\Application\Actions;

use App\Modules\Audit\Models\AuditLog;
use App\Modules\IdentityAccess\Domain\Enums\PasswordResetStatus;
use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\SessionEndReason;
use App\Modules\IdentityAccess\Domain\Enums\SessionEventType;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\PasswordReset;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\IdentityAccess\Services\PasswordPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ResetOperatorPassword
{
    public function __construct(
        private PasswordPolicy $passwordPolicy,
        private RevokeAllUserSessions $revokeAllUserSessions,
    ) {}

    public function execute(
        User $target,
        User $actor,
        string $adminPassword,
        ?string $reason = null,
        ?string $correlationId = null,
    ): array {
        if (! Hash::check($adminPassword, $actor->password)) {
            throw ValidationException::withMessages([
                'admin_password' => 'La contraseña del administrador no es correcta.',
            ]);
        }

        $temporaryPassword = $this->passwordPolicy->generateTemporary();
        $now = now();
        $correlationId ??= (string) Str::uuid();

        $reset = DB::transaction(function () use (
            $target,
            $actor,
            $temporaryPassword,
            $reason,
            $now,
            $correlationId,
        ) {
            $lockedTarget = User::query()->whereKey($target->id)->lockForUpdate()->firstOrFail();

            if (
                $actor->role !== Role::ADMINISTRADOR_PROPIETARIO
                || $lockedTarget->role !== Role::OPERADOR
                || $actor->organization_id !== $lockedTarget->organization_id
            ) {
                throw new AuthorizationException();
            }

            if ($lockedTarget->status !== UserStatus::ACTIVE) {
                throw new RuntimeException('El operador debe estar activo antes del restablecimiento.');
            }

            $priorResets = PasswordReset::query()
                ->where('user_id', $lockedTarget->id)
                ->whereIn('status', [PasswordResetStatus::PENDING, PasswordResetStatus::CONSUMED])
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($priorResets as $prior) {
                $nextStatus = $prior->status === PasswordResetStatus::PENDING
                    && $now->greaterThanOrEqualTo($prior->expires_at)
                    ? PasswordResetStatus::EXPIRED
                    : PasswordResetStatus::SUPERSEDED;
                $timeColumn = $nextStatus === PasswordResetStatus::SUPERSEDED
                    ? ['superseded_at' => $now]
                    : [];
                $priorStatus = $prior->status->value;
                $prior->update(['status' => $nextStatus, ...$timeColumn]);
                $this->audit(
                    $lockedTarget,
                    $actor,
                    'password_reset.'.strtolower($nextStatus->value),
                    ['reset_public_id' => $prior->public_id, 'status' => $priorStatus],
                    ['reset_public_id' => $prior->public_id, 'status' => $nextStatus->value],
                    $reason,
                    $correlationId,
                    $now,
                );
            }

            $lockedTarget->update([
                'password' => Hash::make($temporaryPassword),
                'password_changed_at' => null,
            ]);

            $reset = PasswordReset::create([
                'organization_id' => $lockedTarget->organization_id,
                'user_id' => $lockedTarget->id,
                'initiated_by_user_id' => $actor->id,
                'status' => PasswordResetStatus::PENDING,
                'issued_at' => $now,
                'expires_at' => $now->copy()->addSeconds(
                    config('session-security.password_reset.ttl_seconds', 3600)
                ),
                'reason' => $reason,
            ]);

            $revokedCount = $this->revokeAllUserSessions->execute(
                $lockedTarget,
                SessionEndReason::PASSWORD_RESET,
                SessionEventType::PASSWORD_RESET_REVOKED,
            );

            $this->audit(
                $lockedTarget,
                $actor,
                'password_reset.issued',
                null,
                [
                    'reset_public_id' => $reset->public_id,
                    'status' => PasswordResetStatus::PENDING->value,
                    'issued_at' => $reset->issued_at->toIso8601String(),
                    'expires_at' => $reset->expires_at->toIso8601String(),
                ],
                $reason,
                $correlationId,
                $now,
            );
            $this->audit(
                $lockedTarget,
                $actor,
                'password_reset.sessions_revoked',
                null,
                ['count' => $revokedCount, 'reason' => SessionEndReason::PASSWORD_RESET->value],
                $reason,
                $correlationId,
                $now,
            );

            return $reset;
        });

        return [
            'temporary_password' => $temporaryPassword,
            'reset' => $reset,
        ];
    }

    private function audit(
        User $target,
        ?User $actor,
        string $action,
        ?array $before,
        ?array $after,
        ?string $reason,
        string $correlationId,
        $now,
    ): void {
        AuditLog::create([
            'organization_id' => $target->organization_id,
            'actor_user_id' => $actor?->id,
            'action' => $action,
            'entity_type' => User::class,
            'entity_id' => $target->id,
            'before_values' => $before,
            'after_values' => $after,
            'reason' => $reason,
            'occurred_at' => $now,
            'correlation_id' => $correlationId,
            'created_at' => $now,
        ]);
    }
}
