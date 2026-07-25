<?php

namespace App\Modules\Agents\Models;

use Illuminate\Database\Eloquent\Model;

class UserAgentAssignment extends Model
{
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $fillable = [
        'user_id', 'agent_id', 'assigned_by', 'starts_at',
        'ends_at', 'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Modules\IdentityAccess\Models\User::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(\App\Modules\IdentityAccess\Models\User::class, 'assigned_by');
    }
}
