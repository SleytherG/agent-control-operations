<?php

namespace App\Modules\DailyClosing\Models;

use Illuminate\Database\Eloquent\Model;

class DailyClosureOperation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'daily_closure_id', 'operation_id', 'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function dailyClosure()
    {
        return $this->belongsTo(DailyClosure::class);
    }

    public function operation()
    {
        return $this->belongsTo(\App\Modules\Operations\Models\Operation::class);
    }
}
