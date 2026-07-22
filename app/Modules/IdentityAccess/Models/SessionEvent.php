<?php

namespace App\Modules\IdentityAccess\Models;

use Illuminate\Database\Eloquent\Model;

class SessionEvent extends Model
{
    public $timestamps = false;

    protected $casts = [
        'occurred_at' => 'datetime',
        'context' => 'array',
    ];

    protected $fillable = [
        'auth_session_id', 'user_id', 'type', 'occurred_at', 'context',
    ];

    public function session()
    {
        return $this->belongsTo(AuthSession::class, 'auth_session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
