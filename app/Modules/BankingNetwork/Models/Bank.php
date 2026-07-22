<?php

namespace App\Modules\BankingNetwork\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $casts = [
        'is_active' => 'boolean',
        'deactivated_at' => 'datetime',
    ];

    protected $fillable = [
        'organization_id', 'code', 'name', 'is_active', 'deactivated_at',
    ];

    public function organization()
    {
        return $this->belongsTo(\App\Modules\Organization\Models\Organization::class);
    }

    public function bankAgents()
    {
        return $this->hasMany(BankAgent::class);
    }
}
