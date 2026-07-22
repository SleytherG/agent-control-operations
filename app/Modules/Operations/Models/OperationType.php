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
    ];

    protected $fillable = [
        'organization_id', 'bank_id', 'name', 'description',
        'cash_direction', 'is_active', 'deactivated_at',
    ];

    protected static function newFactory(): OperationTypeFactory
    {
        return OperationTypeFactory::new();
    }

    public function organization()
    {
        return $this->belongsTo(\App\Modules\Organization\Models\Organization::class);
    }

    public function bank()
    {
        return $this->belongsTo(\App\Modules\BankingNetwork\Models\Bank::class);
    }
}
