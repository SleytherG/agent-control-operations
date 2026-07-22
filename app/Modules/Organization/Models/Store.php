<?php

namespace App\Modules\Organization\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $casts = [
        'is_active' => 'boolean',
        'deactivated_at' => 'datetime',
    ];

    protected $fillable = [
        'organization_id', 'district_id', 'code', 'name', 'address',
        'is_active', 'deactivated_at',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function bankAgents()
    {
        return $this->hasMany(\App\Modules\BankingNetwork\Models\BankAgent::class);
    }

    public function activeBankAgents()
    {
        return $this->bankAgents()->where('is_active', true);
    }

    public function hasActiveAgents(): bool
    {
        return $this->activeBankAgents()->exists();
    }
}
