<?php

namespace App\Modules\Organization\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $casts = [
        'is_active' => 'boolean',
        'deactivated_at' => 'datetime',
    ];

    protected $fillable = [
        'public_id', 'name', 'timezone', 'is_active', 'deactivated_at',
    ];
}
