<?php

namespace App\Modules\IdentityAccess\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use App\Modules\IdentityAccess\Models\User;

class CreateOperatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('createOperator', User::class);
    }

    public function rules(): array
    {
        $orgId = auth()->user()->organization_id;

        return [
            'username' => ['required', 'string', 'max:100', 'unique:users,username_normalized,NULL,id,organization_id,' . $orgId],
            'email' => ['required', 'string', 'email', 'max:254', 'unique:users,email_normalized,NULL,id,organization_id,' . $orgId],
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}
