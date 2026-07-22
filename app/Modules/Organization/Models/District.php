<?php

namespace App\Modules\Organization\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $casts = [
        'is_active' => 'boolean',
        'deactivated_at' => 'datetime',
    ];

    protected $fillable = [
        'organization_id', 'province_id', 'name', 'is_active', 'deactivated_at',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function stores()
    {
        return $this->hasMany(Store::class);
    }
}
