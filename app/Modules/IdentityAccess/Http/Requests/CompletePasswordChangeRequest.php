<?php

namespace App\Modules\IdentityAccess\Http\Requests;

use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Services\PasswordPolicy;
use Illuminate\Foundation\Http\FormRequest;

class CompletePasswordChangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $rules = [
            'password' => [
                'required',
                'string',
                app(PasswordPolicy::class)->permanentRule(),
                'confirmed',
            ],
        ];

        $session = AuthSession::find($this->input('auth_session_id'));
        if (! $session?->password_reset_id) {
            $rules['current_password'] = ['required', 'string'];
        }

        return $rules;
    }
}
