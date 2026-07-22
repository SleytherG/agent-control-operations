<?php

namespace App\Modules\Organization\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $casts = [
        'is_active' => 'boolean',
        'deactivated_at' => 'datetime',
    ];

    protected $fillable = [
        'organization_id', 'name', 'is_active', 'deactivated_at',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function provinces()
    {
        return $this->hasMany(Province::class);
    }

    public function activeProvinces()
    {
        return $this->provinces()->where('is_active', true);
    }
}
