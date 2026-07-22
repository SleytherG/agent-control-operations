<?php

namespace App\Modules\Organization\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $casts = [
        'is_active' => 'boolean',
        'deactivated_at' => 'datetime',
    ];

    protected $fillable = [
        'organization_id', 'region_id', 'name', 'is_active', 'deactivated_at',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function districts()
    {
        return $this->hasMany(District::class);
    }

    public function activeDistricts()
    {
        return $this->districts()->where('is_active', true);
    }
}
