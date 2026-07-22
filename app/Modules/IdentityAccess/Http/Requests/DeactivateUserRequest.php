<?php

namespace App\Modules\IdentityAccess\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeactivateUserRequest extends FormRequest
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

    public function deactivationReason(): string
    {
        return trim($this->input('reason'));
    }
}
