<?php

namespace App\Modules\IdentityAccess\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string', 'max:254'],
            'password' => ['required', 'string'],
        ];
    }

    public function normalizedIdentifier(): string
    {
        return Str::lower(trim($this->input('identifier')));
    }
}
