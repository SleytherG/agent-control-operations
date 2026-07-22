<?php

namespace App\Modules\Operations\Application\Actions;

use App\Modules\BankingNetwork\Models\BankAgent;
use App\Modules\BankingNetwork\Models\UserBankAgentAssignment;
use App\Modules\Operations\Models\Operation;
use Illuminate\Support\Facades\DB;

class RegisterOperation
{
    public function execute(array $data, int $userId, int $organizationId): Operation
    {
        $this->validateAssignment($data['bank_agent_id'], $userId);

        $bankAgent = BankAgent::findOrFail($data['bank_agent_id']);

        $operation = DB::transaction(function () use ($data, $userId, $organizationId, $bankAgent) {
            $operation = Operation::create([
                'organization_id' => $organizationId,
                'store_id' => $bankAgent->store_id,
                'bank_agent_id' => $data['bank_agent_id'],
                'operation_type_id' => $data['operation_type_id'],
                'user_id' => $userId,
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? config('operations.default_currency', 'PEN'),
                'effective_at' => $data['effective_at'],
                'recorded_at' => now(),
                'status' => Operation::STATUS_ACTIVE,
                'reference' => $data['reference'] ?? null,
                'observation' => $data['observation'] ?? null,
                'idempotency_key' => $data['idempotency_key'],
            ]);

            return $operation;
        });

        return $operation;
    }

    private function validateAssignment(int $bankAgentId, int $userId): void
    {
        $assignment = UserBankAgentAssignment::where('user_id', $userId)
            ->where('bank_agent_id', $bankAgentId)
            ->where('is_active', true)
            ->first();

        if (! $assignment) {
            throw new \RuntimeException('El usuario no tiene una asignación activa a este agente bancario.');
        }
    }
}
