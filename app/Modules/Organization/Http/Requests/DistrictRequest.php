<?php

namespace App\Modules\Organization\Http\Requests;

use App\Modules\Organization\Models\District;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class DistrictRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->route('district')) {
            return Gate::allows('update', $this->route('district'));
        }

        return Gate::allows('create', District::class);
    }

    public function rules(): array
    {
        $districtId = $this->route('district')?->id;
        $provinceId = $this->route('province')->id;

        return [
            'name' => ['required', 'string', 'max:160', 'unique:districts,name,' . ($districtId ?? 'NULL') . ',id,province_id,' . $provinceId],
            'is_active' => ['boolean'],
        ];
    }
}
