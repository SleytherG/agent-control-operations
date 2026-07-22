<?php

namespace App\Modules\BankingNetwork\Models;

use Illuminate\Database\Eloquent\Model;

class UserBankAgentAssignment extends Model
{
    protected $casts = [
        'assigned_at' => 'datetime',
        'unassigned_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $fillable = [
        'user_id', 'bank_agent_id', 'assigned_by', 'assigned_at',
        'unassigned_at', 'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Modules\IdentityAccess\Models\User::class);
    }

    public function bankAgent()
    {
        return $this->belongsTo(BankAgent::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(\App\Modules\IdentityAccess\Models\User::class, 'assigned_by');
    }
}
