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
            'bank_agent_id' => ['required', 'exists:bank_agents,id'],
            'business_date' => ['required', 'date', 'before_or_equal:today'],
            'regenerate' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'bank_agent_id.required' => 'El agente bancario es obligatorio.',
            'bank_agent_id.exists' => 'El agente bancario seleccionado no existe.',
            'business_date.required' => 'La fecha del cierre es obligatoria.',
            'business_date.date' => 'La fecha del cierre no es válida.',
            'business_date.before_or_equal' => 'La fecha del cierre no puede ser futura.',
        ];
    }
}
