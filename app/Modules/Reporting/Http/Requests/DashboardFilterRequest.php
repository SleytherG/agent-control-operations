<?php

namespace App\Modules\Reporting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DashboardFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'period' => ['nullable', 'string', 'in:day,week,month,quarter,semester,year'],
            'date' => ['nullable', 'date'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'province_id' => ['nullable', 'integer', 'exists:provinces,id'],
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'bank_id' => ['nullable', 'integer', 'exists:banks,id'],
            'bank_agent_id' => ['nullable', 'integer', 'exists:bank_agents,id'],
            'operator_id' => ['nullable', 'integer', 'exists:users,id'],
            'operation_type_id' => ['nullable', 'integer', 'exists:operation_types,id'],
            'include_annulled' => ['nullable', 'boolean'],
            'operator_ids' => ['nullable', 'array'],
            'operator_ids.*' => ['integer', 'exists:users,id'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
