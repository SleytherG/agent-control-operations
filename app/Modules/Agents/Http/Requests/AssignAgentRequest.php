<?php

namespace App\Modules\Agents\Http\Requests;

use App\Modules\Agents\Models\Agent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class AssignAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', Agent::class);
    }

    public function rules(): array
    {
        return [
            'agent_id' => [
                'required',
                'exists:agents,id,is_active,1',
                Rule::unique('user_agent_assignments', 'agent_id')
                    ->where('user_id', $this->route('user')->id)
                    ->where('is_active', true),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'agent_id.unique' => 'El operador ya tiene una asignación activa a este agente.',
        ];
    }
}
