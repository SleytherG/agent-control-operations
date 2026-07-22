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
                    ->where('bank_id', $this->input('bank_id'))
                    ->ignore($typeId),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'bank_id' => ['nullable', 'exists:banks,id,is_active,1'],
            'cash_direction' => ['required', 'in:ENTRADA,SALIDA,NEUTRA,POR_CONFIRMAR'],
        ];
    }
}
