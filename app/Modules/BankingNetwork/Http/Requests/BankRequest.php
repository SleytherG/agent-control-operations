<?php

namespace App\Modules\BankingNetwork\Http\Requests;

use App\Modules\BankingNetwork\Models\Bank;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class BankRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->route('bank')) {
            return Gate::allows('update', $this->route('bank'));
        }

        return Gate::allows('create', Bank::class);
    }

    public function rules(): array
    {
        $bankId = $this->route('bank')?->id;
        $orgId = auth()->user()->organization_id;

        return [
            'code' => ['required', 'string', 'max:20', 'unique:banks,code,' . ($bankId ?? 'NULL') . ',id,organization_id,' . $orgId],
            'name' => ['required', 'string', 'max:200'],
        ];
    }
}
