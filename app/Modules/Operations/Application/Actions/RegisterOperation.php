<?php

namespace App\Modules\Operations\Application\Actions;

use App\Modules\Agents\Models\Agent;
use App\Modules\Agents\Models\UserAgentAssignment;
use App\Modules\DailyClosing\Models\DailyClosure;
use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\Operations\Models\Operation;
use App\Modules\Operations\Models\OperationType;
use App\Modules\Operations\Services\InternalCodeGenerator;
use Illuminate\Support\Facades\DB;

class RegisterOperation
{
    public function execute(array $data, int $userId, int $organizationId): Operation
    {
        $agentId = $data['agent_id'];
        $this->validateAssignment($agentId, $userId);

        $effectiveAt = $this->resolveEffectiveAt($data['effective_at'] ?? now()->format('Y-m-d H:i:s'), $userId);

        $this->validateNotConfirmed($agentId, $effectiveAt);

        $agent = Agent::findOrFail($agentId);
        $type = OperationType::findOrFail($data['operation_type_id']);

        $cashDelta = $type->cash_multiplier * $data['amount'];
        $digitalDelta = $type->digital_multiplier * $data['amount'];

        $codeGenerator = app(InternalCodeGenerator::class);

        $operation = DB::transaction(function () use ($data, $userId, $organizationId, $agent, $cashDelta, $digitalDelta, $codeGenerator) {
            $internalCode = $codeGenerator->generate($data['effective_at']);

            $operation = Operation::create([
                'organization_id' => $organizationId,
                'agent_id' => $agent->id,
                'operation_type_id' => $data['operation_type_id'],
                'user_id' => $userId,
                'internal_code' => $internalCode,
                'customer_name' => $data['customer_name'] ?? null,
                'amount' => $data['amount'],
                'cash_delta' => $cashDelta,
                'digital_delta' => $digitalDelta,
                'currency' => $data['currency'] ?? config('operations.default_currency', 'PEN'),
                'effective_at' => $data['effective_at'],
                'recorded_at' => now(),
                'status' => Operation::STATUS_ACTIVE,
                'observation' => $data['notes'] ?? null,
                'idempotency_key' => $data['idempotency_key'],
            ]);

            return $operation;
        });

        return $operation;
    }

    private function validateAssignment(int $agentId, int $userId): void
    {
        $assignment = UserAgentAssignment::where('user_id', $userId)
            ->where('agent_id', $agentId)
            ->where('is_active', true)
            ->first();

        if (! $assignment) {
            throw new \RuntimeException('El usuario no tiene una asignación activa a este agente.');
        }
    }

    private function resolveEffectiveAt(string $effectiveAt, int $userId): string
    {
        $user = \App\Modules\IdentityAccess\Models\User::find($userId);

        if (! $user || $user->role !== Role::ADMINISTRADOR_PROPIETARIO) {
            return now()->format('Y-m-d H:i:s');
        }

        $windowHours = config('operations.retroactive_window_hours', 24);
        $now = now();
        $requested = \Carbon\Carbon::parse($effectiveAt);

        if ($requested->isAfter($now)) {
            return $now->format('Y-m-d H:i:s');
        }

        if ($requested->diffInHours($now) > $windowHours) {
            return $now->format('Y-m-d H:i:s');
        }

        return $requested->format('Y-m-d H:i:s');
    }

    private function validateNotConfirmed(int $agentId, string $effectiveAt): void
    {
        $confirmedClosure = DailyClosure::where('agent_id', $agentId)
            ->whereDate('business_date', date('Y-m-d', strtotime($effectiveAt)))
            ->where('status', DailyClosure::STATUS_CONFIRMADO)
            ->exists();

        if ($confirmedClosure) {
            throw new \RuntimeException('No se pueden registrar operaciones en una fecha con cierre confirmado.');
        }
    }
}
