<?php

namespace App\Modules\Operations\Application\Actions;

use App\Modules\Operations\Models\Operation;
use App\Modules\Audit\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AnnulOperation
{
    public function execute(Operation $operation, string $reason, int $actorId, bool $isAdmin): Operation
    {
        if (! $operation->isActive()) {
            throw new \RuntimeException('La operación ya se encuentra anulada.');
        }

        if (! $isAdmin) {
            $hoursSinceRecorded = abs(now()->diffInHours($operation->recorded_at));
            $window = config('operations.annulment_window_hours', 24);

            if ($hoursSinceRecorded > $window) {
                throw new \RuntimeException('La ventana de anulación ha expirado.');
            }
        }

        DB::transaction(function () use ($operation, $reason, $actorId) {
            $before = $operation->only(['status']);

            $operation->update([
                'status' => Operation::STATUS_ANNULLED,
                'annulled_by' => $actorId,
                'annulled_at' => now(),
                'annulment_reason' => $reason,
            ]);

            AuditLog::create([
                'correlation_id' => (string) Str::uuid(),
                'created_at' => now(),
                'organization_id' => $operation->organization_id,
                'actor_user_id' => $actorId,
                'action' => 'operation.annulled',
                'entity_type' => Operation::class,
                'entity_id' => $operation->id,
                'before_values' => $before,
                'after_values' => $operation->only(['status', 'annulled_by', 'annulment_reason']),
                'occurred_at' => now(),
            ]);
        });

        return $operation->refresh();
    }
}
