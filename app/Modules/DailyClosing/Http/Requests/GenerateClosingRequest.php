<?php

namespace App\Modules\DailyClosing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateClosingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'agent_id' => ['nullable', 'exists:agents,id,is_active,1'],
            'bank_agent_id' => ['nullable', 'exists:bank_agents,id'],
            'business_date' => ['required', 'date', 'before_or_equal:today'],
            'opening_cash' => ['nullable', 'numeric', 'min:0'],
            'opening_digital' => ['nullable', 'numeric', 'min:0'],
            'regenerate' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'agent_id.exists' => 'El agente seleccionado no existe o está inactivo.',
            'bank_agent_id.required' => 'El agente es obligatorio.',
            'business_date.required' => 'La fecha del cierre es obligatoria.',
            'business_date.date' => 'La fecha del cierre no es válida.',
            'business_date.before_or_equal' => 'La fecha del cierre no puede ser futura.',
        ];
    }
}
