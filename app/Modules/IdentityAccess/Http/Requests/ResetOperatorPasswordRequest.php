<?php

namespace App\Modules\IdentityAccess\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetOperatorPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'admin_password' => ['required', 'string'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
