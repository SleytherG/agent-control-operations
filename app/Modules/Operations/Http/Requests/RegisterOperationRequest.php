<?php

namespace App\Modules\Operations\Http\Requests;

use App\Modules\Agents\Models\UserAgentAssignment;
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
            'agent_id' => ['nullable', 'exists:agents,id,is_active,1'],
            'operation_type_id' => ['required', 'exists:operation_types,id,is_active,1'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'customer_name' => ['nullable', 'string', 'max:200'],
            'notes' => ['nullable', 'string', 'max:500'],
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
            'idempotency_key' => ['required', 'string', 'max:64'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.gt' => 'El monto debe ser mayor a cero.',
            'agent_id.exists' => 'El agente seleccionado no existe o está inactivo.',
            'operation_type_id.exists' => 'El tipo de operación no está activo o no existe.',
            'effective_at.before_or_equal' => 'La fecha efectiva no puede ser futura.',
        ];
    }
}
