<?php

namespace App\Modules\Audit\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $casts = [
        'occurred_at' => 'datetime',
        'before_values' => 'array',
        'after_values' => 'array',
    ];

    protected $fillable = [
        'organization_id', 'actor_user_id', 'action', 'entity_type', 'entity_id',
        'before_values', 'after_values', 'reason', 'occurred_at', 'correlation_id',
    ];
}
