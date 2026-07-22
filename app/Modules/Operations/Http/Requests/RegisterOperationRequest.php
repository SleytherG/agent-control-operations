<?php

namespace App\Modules\Operations\Http\Requests;

use App\Modules\BankingNetwork\Models\BankAgent;
use App\Modules\BankingNetwork\Models\UserBankAgentAssignment;
use Illuminate\Foundation\Http\FormRequest;

class RegisterOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_agent_id' => ['required', 'exists:bank_agents,id'],
            'operation_type_id' => ['required', 'exists:operation_types,id,is_active,1'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'effective_at' => [
                'required',
                'date',
                'before_or_equal:now',
                function ($attribute, $value, $fail) {
                    $window = now()->subHours(config('operations.retroactive_window_hours', 24));
                    if (strtotime($value) < $window->timestamp) {
                        $fail('La fecha efectiva está fuera de la ventana retroactiva permitida.');
                    }
                },
            ],
            'reference' => ['nullable', 'string', 'max:100'],
            'observation' => ['nullable', 'string', 'max:500'],
            'idempotency_key' => ['required', 'string', 'max:64'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.gt' => 'El monto debe ser mayor a cero.',
            'bank_agent_id.exists' => 'El agente bancario seleccionado no existe.',
            'operation_type_id.exists' => 'El tipo de operación no está activo o no existe.',
            'effective_at.before_or_equal' => 'La fecha efectiva no puede ser futura.',
        ];
    }
}
