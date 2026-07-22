<?php

namespace App\Modules\Organization\Http\Requests;

use App\Modules\Organization\Models\Region;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class RegionRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->route('region')) {
            return Gate::allows('update', $this->route('region'));
        }

        return Gate::allows('create', Region::class);
    }

    public function rules(): array
    {
        $orgId = auth()->user()->organization_id;
        $regionId = $this->route('region')?->id;

        return [
            'name' => ['required', 'string', 'max:160', 'unique:regions,name,' . ($regionId ?? 'NULL') . ',id,organization_id,' . $orgId],
            'is_active' => ['boolean'],
        ];
    }
}
