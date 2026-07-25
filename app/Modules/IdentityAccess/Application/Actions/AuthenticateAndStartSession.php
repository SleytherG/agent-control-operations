<?php

namespace App\Modules\IdentityAccess\Application\Actions;

use App\Modules\Audit\Models\AuditLog;
use App\Modules\IdentityAccess\Domain\Enums\PasswordResetStatus;
use App\Modules\IdentityAccess\Domain\Enums\SessionEventType;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\PasswordReset;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AuthenticateAndStartSession
{
    public function __construct(private StartAuthSession $startAuthSession) {}

    public function execute(
        string $identifier,
        string $password,
        ?string $ip = null,
        ?string $userAgent = null,
        ?string $correlationId = null,
    ): ?array {
        $normalized = mb_strtolower(trim($identifier));
        $correlationId ??= (string) Str::uuid();

        return DB::transaction(function () use (
            $normalized,
            $password,
            $ip,
            $userAgent,
            $correlationId,
        ) {
            $user = User::query()
                ->where(function ($query) use ($normalized) {
                    $query->where('username_normalized', $normalized)
                        ->orWhere('email_normalized', $normalized);
                })
                ->lockForUpdate()
                ->first();

            if (
                ! $user
                || $user->status !== UserStatus::ACTIVE
                || ! Hash::check($password, $user->password)
            ) {
                return null;
            }

            $reset = null;

            if (Schema::hasTable('password_resets')) {
                $reset = PasswordReset::query()
                    ->where('user_id', $user->id)
                    ->latest('issued_at')
                    ->latest('id')
                    ->lockForUpdate()
                    ->first();
            }

            if ($reset?->status === PasswordResetStatus::PENDING) {
                if (now()->greaterThanOrEqualTo($reset->expires_at)) {
                    $before = ['reset_public_id' => $reset->public_id, 'status' => $reset->status->value];
                    $reset->update(['status' => PasswordResetStatus::EXPIRED]);
                    $this->audit($user, null, 'password_reset.expired', $before, [
                        'reset_public_id' => $reset->public_id,
                        'status' => PasswordResetStatus::EXPIRED->value,
                        'expires_at' => $reset->expires_at->toIso8601String(),
                    ], $correlationId);

                    return null;
                }

                $result = $this->startAuthSession->execute(
                    $user,
                    $ip,
                    $userAgent,
                    $reset,
                    SessionEventType::PASSWORD_RESET_LOGIN,
                );
                $reset->update([
                    'status' => PasswordResetStatus::CONSUMED,
                    'consumed_at' => now(),
                ]);
                $this->audit($user, $user, 'password_reset.consumed', [
                    'reset_public_id' => $reset->public_id,
                    'status' => PasswordResetStatus::PENDING->value,
                ], [
                    'reset_public_id' => $reset->public_id,
                    'status' => PasswordResetStatus::CONSUMED->value,
                    'session_public_id' => $result['session']->public_id,
                    'consumed_at' => $reset->consumed_at->toIso8601String(),
                ], $correlationId);

                return [...$result, 'user' => $user, 'restricted' => true];
            }

            if ($reset && in_array($reset->status, [
                PasswordResetStatus::CONSUMED,
                PasswordResetStatus::EXPIRED,
            ], true)) {
                return null;
            }

            $result = $this->startAuthSession->execute($user, $ip, $userAgent);

            return [...$result, 'user' => $user, 'restricted' => false];
        });
    }

    private function audit(
        User $target,
        ?User $actor,
        string $action,
        ?array $before,
        ?array $after,
        string $correlationId,
    ): void {
        AuditLog::create([
            'organization_id' => $target->organization_id,
            'actor_user_id' => $actor?->id,
            'action' => $action,
            'entity_type' => User::class,
            'entity_id' => $target->id,
            'before_values' => $before,
            'after_values' => $after,
            'occurred_at' => now(),
            'correlation_id' => $correlationId,
            'created_at' => now(),
        ]);
    }
}
