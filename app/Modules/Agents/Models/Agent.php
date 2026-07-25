<?php

namespace App\Modules\Agents\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $casts = [
        'is_active' => 'boolean',
        'deactivated_at' => 'datetime',
    ];

    protected $fillable = [
        'organization_id', 'code', 'name', 'city', 'region', 'province',
        'district', 'address', 'description', 'is_active', 'deactivated_at',
    ];

    public function organization()
    {
        return $this->belongsTo(\App\Modules\Organization\Models\Organization::class);
    }

    public function assignments()
    {
        return $this->hasMany(UserAgentAssignment::class);
    }

    public function activeAssignments()
    {
        return $this->assignments()->where('is_active', true);
    }
}
