<?php

namespace App\Modules\BankingNetwork\Http\Requests;

use App\Modules\BankingNetwork\Models\BankAgent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class BankAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->route('agent')) {
            return Gate::allows('update', $this->route('agent'));
        }

        return Gate::allows('create', BankAgent::class);
    }

    public function rules(): array
    {
        $agentId = $this->route('agent')?->id;
        $orgId = auth()->user()->organization_id;

        return [
            'store_id' => ['required', 'exists:stores,id,is_active,1'],
            'bank_id' => ['required', 'exists:banks,id,is_active,1'],
            'code' => ['required', 'string', 'max:80', 'unique:bank_agents,code,' . ($agentId ?? 'NULL') . ',id,organization_id,' . $orgId],
            'terminal_code' => ['nullable', 'string', 'max:40'],
        ];
    }
}
