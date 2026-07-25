<?php

namespace App\Modules\Operations\Models;

use Database\Factories\Operations\OperationTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationType extends Model
{
    /** @use HasFactory<OperationTypeFactory> */
    use HasFactory;

    protected $casts = [
        'is_active' => 'boolean',
        'deactivated_at' => 'datetime',
        'cash_multiplier' => 'integer',
        'digital_multiplier' => 'integer',
    ];

    protected $fillable = [
        'organization_id', 'name', 'description',
        'cash_multiplier', 'digital_multiplier', 'sort_order',
        'is_active', 'deactivated_at',
    ];

    protected static function newFactory(): OperationTypeFactory
    {
        return OperationTypeFactory::new();
    }

    public function organization()
    {
        return $this->belongsTo(\App\Modules\Organization\Models\Organization::class);
    }
}
