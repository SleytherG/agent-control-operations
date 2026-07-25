<?php

namespace App\Modules\Operations\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OperationTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $typeId = $this->route('type')?->id;
        $orgId = auth()->user()->organization_id;

        return [
            'name' => [
                'required',
                'string',
                'max:160',
                Rule::unique('operation_types', 'name')
                    ->where('organization_id', $orgId)
                    ->ignore($typeId),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'cash_multiplier' => ['required', 'in:-1,0,1'],
            'digital_multiplier' => ['required', 'in:-1,0,1'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
