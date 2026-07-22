<?php

namespace App\Modules\Organization\Http\Requests;

use App\Modules\Organization\Models\Province;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ProvinceRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->route('province')) {
            return Gate::allows('update', $this->route('province'));
        }

        return Gate::allows('create', Province::class);
    }

    public function rules(): array
    {
        $provinceId = $this->route('province')?->id;
        $regionId = $this->route('region')->id;

        return [
            'name' => ['required', 'string', 'max:160', 'unique:provinces,name,' . ($provinceId ?? 'NULL') . ',id,region_id,' . $regionId],
            'is_active' => ['boolean'],
        ];
    }
}
