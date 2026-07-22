<?php

namespace App\Modules\IdentityAccess\Models;

use App\Modules\IdentityAccess\Domain\Enums\AuthSessionStatus;
use App\Modules\IdentityAccess\Domain\Enums\SessionEndReason;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuthSession extends Model
{
    protected $casts = [
        'started_at' => 'datetime',
        'access_expires_at' => 'datetime',
        'absolute_expires_at' => 'datetime',
        'last_refreshed_at' => 'datetime',
        'ended_at' => 'datetime',
        'status' => AuthSessionStatus::class,
        'end_reason' => SessionEndReason::class,
    ];

    protected $fillable = [
        'public_id', 'user_id', 'status', 'started_at', 'access_expires_at',
        'absolute_expires_at', 'last_refreshed_at', 'ended_at', 'end_reason',
        'ip_hash', 'user_agent_summary',
    ];

    protected static function booted(): void
    {
        static::creating(function (AuthSession $session) {
            $session->public_id ??= (string) Str::uuid();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function refreshTokens()
    {
        return $this->hasMany(AuthRefreshToken::class);
    }

    public function events()
    {
        return $this->hasMany(SessionEvent::class);
    }

    public function isActive(): bool
    {
        return $this->status === AuthSessionStatus::ACTIVE;
    }

    public function activeRefreshToken()
    {
        return $this->hasOne(AuthRefreshToken::class)
            ->where('state', RefreshTokenState::ACTIVE);
    }
}
