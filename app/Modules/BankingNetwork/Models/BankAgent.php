<?php

namespace App\Modules\BankingNetwork\Models;

use Illuminate\Database\Eloquent\Model;

class BankAgent extends Model
{
    protected $casts = [
        'is_active' => 'boolean',
        'deactivated_at' => 'datetime',
    ];

    protected $fillable = [
        'organization_id', 'store_id', 'bank_id', 'code', 'terminal_code',
        'is_active', 'deactivated_at',
    ];

    public function organization()
    {
        return $this->belongsTo(\App\Modules\Organization\Models\Organization::class);
    }

    public function store()
    {
        return $this->belongsTo(\App\Modules\Organization\Models\Store::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function assignments()
    {
        return $this->hasMany(UserBankAgentAssignment::class);
    }

    public function activeAssignments()
    {
        return $this->assignments()->where('is_active', true);
    }
}
