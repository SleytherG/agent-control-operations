<?php

namespace App\Modules\BankingNetwork\Http\Requests;

use App\Modules\BankingNetwork\Models\BankAgent;
use App\Modules\BankingNetwork\Models\UserBankAgentAssignment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class AssignOperatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_agent_id' => ['required', 'exists:bank_agents,id,is_active,1'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $userId = $this->route('user')->id;
            $bankAgentId = $this->input('bank_agent_id');

            $existing = UserBankAgentAssignment::where('user_id', $userId)
                ->where('bank_agent_id', $bankAgentId)
                ->where('is_active', true)
                ->exists();

            if ($existing) {
                $validator->errors()->add('bank_agent_id', 'El operador ya tiene una asignación activa a este agente.');
            }
        });
    }
}
