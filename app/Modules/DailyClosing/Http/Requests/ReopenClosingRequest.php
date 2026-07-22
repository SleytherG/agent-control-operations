<?php

namespace App\Modules\DailyClosing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReopenClosingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'El motivo de reapertura es obligatorio.',
            'reason.max' => 'El motivo no debe exceder los 500 caracteres.',
        ];
    }
}
