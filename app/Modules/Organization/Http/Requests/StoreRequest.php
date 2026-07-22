<?php

namespace App\Modules\Organization\Http\Requests;

use App\Modules\Organization\Models\Store;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->route('store')) {
            return Gate::allows('update', $this->route('store'));
        }

        return Gate::allows('create', Store::class);
    }

    public function rules(): array
    {
        $storeId = $this->route('store')?->id;

        return [
            'district_id' => ['required', 'exists:districts,id,is_active,1'],
            'code' => ['required', 'string', 'max:80', 'unique:stores,code,' . ($storeId ?? 'NULL') . ',id,organization_id,' . auth()->user()->organization_id],
            'name' => ['required', 'string', 'max:200'],
            'address' => ['nullable', 'string', 'max:500'],
        ];
    }
}
