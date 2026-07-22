<?php

namespace App\Modules\IdentityAccess\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListSessionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['integer', 'min:1'],
            'from' => ['date'],
            'to' => ['date', 'after_or_equal:from'],
            'status' => ['string'],
            'user_id' => ['integer', 'exists:users,id'],
            'per_page' => ['integer', 'min:1', 'max:' . config('session-security.history.max_page_size', 100)],
        ];
    }
}
