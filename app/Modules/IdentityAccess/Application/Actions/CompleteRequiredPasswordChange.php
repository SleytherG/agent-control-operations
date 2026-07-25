<?php

namespace App\Modules\IdentityAccess\Application\Actions;

use App\Modules\Audit\Models\AuditLog;
use App\Modules\IdentityAccess\Domain\Enums\AuthSessionStatus;
use App\Modules\IdentityAccess\Domain\Enums\PasswordResetStatus;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\PasswordReset;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CompleteRequiredPasswordChange
{
    public function execute(
        int $sessionId,
        User $authenticatedUser,
        string $newPassword,
        ?string $correlationId = null,
    ): User {
        $correlationId ??= (string) Str::uuid();

        return DB::transaction(function () use (
            $sessionId,
            $authenticatedUser,
            $newPassword,
            $correlationId,
        ) {
            $session = AuthSession::query()->whereKey($sessionId)->lockForUpdate()->firstOrFail();
            $reset = PasswordReset::query()
                ->whereKey($session->password_reset_id)
                ->lockForUpdate()
                ->firstOrFail();
            $user = User::query()->whereKey($authenticatedUser->id)->lockForUpdate()->firstOrFail();

            if (
                $session->user_id !== $user->id
                || $session->status !== AuthSessionStatus::ACTIVE
                || $reset->user_id !== $user->id
                || $reset->status !== PasswordResetStatus::CONSUMED
            ) {
                throw ValidationException::withMessages([
                    'password' => 'La sesión de cambio ya no es válida.',
                ]);
            }

            if (Hash::check($newPassword, $user->password)) {
                throw ValidationException::withMessages([
                    'password' => 'La nueva contraseña debe ser diferente de la temporal.',
                ]);
            }

            $user->update([
                'password' => Hash::make($newPassword),
                'password_changed_at' => now(),
            ]);
            $reset->update([
                'status' => PasswordResetStatus::COMPLETED,
                'completed_at' => now(),
            ]);

            AuditLog::create([
                'organization_id' => $user->organization_id,
                'actor_user_id' => $user->id,
                'action' => 'password_reset.completed',
                'entity_type' => User::class,
                'entity_id' => $user->id,
                'before_values' => [
                    'reset_public_id' => $reset->public_id,
                    'status' => PasswordResetStatus::CONSUMED->value,
                ],
                'after_values' => [
                    'reset_public_id' => $reset->public_id,
                    'status' => PasswordResetStatus::COMPLETED->value,
                    'completed_at' => $reset->completed_at->toIso8601String(),
                ],
                'occurred_at' => now(),
                'correlation_id' => $correlationId,
                'created_at' => now(),
            ]);

            return $user;
        });
    }
}
