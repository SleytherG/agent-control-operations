<?php

namespace App\Modules\Agents\Http\Requests;

use App\Modules\Agents\Models\Agent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class AgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->route('agent')) {
            return Gate::allows('update', $this->route('agent'));
        }

        return Gate::allows('create', Agent::class);
    }

    public function rules(): array
    {
        $agentId = $this->route('agent')?->id;
        $orgId = auth()->user()->organization_id;

        return [
            'code' => ['required', 'string', 'max:80', 'unique:agents,code,' . ($agentId ?? 'NULL') . ',id,organization_id,' . $orgId],
            'name' => ['required', 'string', 'max:200'],
            'city' => ['required', 'string', 'max:160'],
            'region' => ['nullable', 'string', 'max:160'],
            'province' => ['nullable', 'string', 'max:160'],
            'district' => ['nullable', 'string', 'max:160'],
            'address' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
        ];
    }
}
