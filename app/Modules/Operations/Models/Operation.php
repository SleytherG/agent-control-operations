<?php

namespace App\Modules\Operations\Models;

use Database\Factories\Operations\OperationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    /** @use HasFactory<OperationFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_ANNULLED = 'ANNULLED';

    protected $casts = [
        'amount' => 'decimal:2',
        'effective_at' => 'datetime',
        'recorded_at' => 'datetime',
        'annulled_at' => 'datetime',
    ];

    protected $fillable = [
        'organization_id', 'store_id', 'bank_agent_id', 'operation_type_id',
        'user_id', 'amount', 'currency', 'effective_at', 'recorded_at',
        'status', 'reference', 'observation', 'annulled_by', 'annulled_at',
        'annulment_reason', 'idempotency_key',
    ];

    protected static function newFactory(): OperationFactory
    {
        return OperationFactory::new();
    }

    public function organization()
    {
        return $this->belongsTo(\App\Modules\Organization\Models\Organization::class);
    }

    public function store()
    {
        return $this->belongsTo(\App\Modules\Organization\Models\Store::class);
    }

    public function bankAgent()
    {
        return $this->belongsTo(\App\Modules\BankingNetwork\Models\BankAgent::class);
    }

    public function operationType()
    {
        return $this->belongsTo(OperationType::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Modules\IdentityAccess\Models\User::class);
    }

    public function annulledBy()
    {
        return $this->belongsTo(\App\Modules\IdentityAccess\Models\User::class, 'annulled_by');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isAnnulled(): bool
    {
        return $this->status === self::STATUS_ANNULLED;
    }
}
