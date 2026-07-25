<?php

namespace App\Modules\IdentityAccess\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\IdentityAccess\Application\Actions\ListPasswordResetAudit;
use App\Modules\IdentityAccess\Domain\Enums\PasswordResetStatus;
use App\Modules\IdentityAccess\Http\Requests\ListPasswordResetAuditRequest;
use App\Modules\IdentityAccess\Models\PasswordReset;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetAuditController extends Controller
{
    public function __invoke(
        ListPasswordResetAuditRequest $request,
        User $user,
        ListPasswordResetAudit $listPasswordResetAudit,
    ): View {
        Gate::authorize('viewPasswordResetAudit', $user);
        $this->resolveExpired($request->user(), $user, $request->header('X-Correlation-ID'));

        return view('identity-access.password-resets.index', [
            'operator' => $user,
            'events' => $listPasswordResetAudit->execute(
                $request->user(),
                $user,
                $request->validated(),
            ),
            'statuses' => PasswordResetStatus::cases(),
        ]);
    }

    private function resolveExpired(User $actor, User $target, ?string $correlationId): void
    {
        DB::transaction(function () use ($actor, $target, $correlationId) {
            $resets = PasswordReset::query()
                ->where('user_id', $target->id)
                ->where('status', PasswordResetStatus::PENDING)
                ->where('expires_at', '<=', now())
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($resets as $reset) {
                $reset->update(['status' => PasswordResetStatus::EXPIRED]);
                AuditLog::create([
                    'organization_id' => $target->organization_id,
                    'actor_user_id' => $actor->id,
                    'action' => 'password_reset.expired',
                    'entity_type' => User::class,
                    'entity_id' => $target->id,
                    'before_values' => [
                        'reset_public_id' => $reset->public_id,
                        'status' => PasswordResetStatus::PENDING->value,
                    ],
                    'after_values' => [
                        'reset_public_id' => $reset->public_id,
                        'status' => PasswordResetStatus::EXPIRED->value,
                        'expires_at' => $reset->expires_at->toIso8601String(),
                    ],
                    'occurred_at' => now(),
                    'correlation_id' => $correlationId ?: (string) Str::uuid(),
                    'created_at' => now(),
                ]);
            }
        });
    }
}
