<?php

namespace App\Modules\DailyClosing\Models;

use Illuminate\Database\Eloquent\Model;

class DailyClosure extends Model
{
    public const STATUS_ACTIVO = 'ACTIVO';
    public const STATUS_CONFIRMADO = 'CONFIRMADO';
    public const STATUS_REABIERTO = 'REABIERTO';

    protected $casts = [
        'business_date' => 'date',
        'operation_count' => 'integer',
        'gross_amount' => 'decimal:2',
        'cash_in' => 'decimal:2',
        'cash_out' => 'decimal:2',
        'net_movement' => 'decimal:2',
        'has_pending_confirm' => 'boolean',
        'confirmed_at' => 'datetime',
        'reopened_at' => 'datetime',
    ];

    protected $fillable = [
        'organization_id', 'store_id', 'bank_agent_id', 'business_date',
        'status', 'operation_count', 'gross_amount', 'cash_in', 'cash_out',
        'net_movement', 'has_pending_confirm',
        'confirmed_by', 'confirmed_at',
        'reopened_by', 'reopened_at', 'reopen_reason',
    ];

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

    public function confirmedBy()
    {
        return $this->belongsTo(\App\Modules\IdentityAccess\Models\User::class, 'confirmed_by');
    }

    public function reopenedBy()
    {
        return $this->belongsTo(\App\Modules\IdentityAccess\Models\User::class, 'reopened_by');
    }

    public function operations()
    {
        return $this->belongsToMany(
            \App\Modules\Operations\Models\Operation::class,
            'daily_closure_operations',
            'daily_closure_id',
            'operation_id'
        );
    }

    public function isActivo(): bool
    {
        return $this->status === self::STATUS_ACTIVO;
    }

    public function isConfirmado(): bool
    {
        return $this->status === self::STATUS_CONFIRMADO;
    }

    public function isReabierto(): bool
    {
        return $this->status === self::STATUS_REABIERTO;
    }

    public function confirm(int $userId): void
    {
        if (! $this->isActivo() && ! $this->isReabierto()) {
            throw new \RuntimeException('Solo se puede confirmar un cierre en estado ACTIVO o REABIERTO.');
        }

        $this->update([
            'status' => self::STATUS_CONFIRMADO,
            'confirmed_by' => $userId,
            'confirmed_at' => now(),
        ]);
    }

    public function reopen(int $userId, string $reason): void
    {
        if (! $this->isConfirmado()) {
            throw new \RuntimeException('Solo se puede reabrir un cierre en estado CONFIRMADO.');
        }

        $this->update([
            'status' => self::STATUS_REABIERTO,
            'reopened_by' => $userId,
            'reopened_at' => now(),
            'reopen_reason' => $reason,
        ]);
    }
}
