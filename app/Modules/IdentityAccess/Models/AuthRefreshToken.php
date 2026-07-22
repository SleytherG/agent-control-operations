<?php

namespace App\Modules\IdentityAccess\Models;

use App\Modules\IdentityAccess\Domain\Enums\RefreshTokenState;
use Illuminate\Database\Eloquent\Model;

class AuthRefreshToken extends Model
{
    protected $casts = [
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
        'consumed_at' => 'datetime',
        'revoked_at' => 'datetime',
        'state' => RefreshTokenState::class,
    ];

    protected $fillable = [
        'auth_session_id', 'token_hash', 'generation', 'state',
        'issued_at', 'expires_at', 'consumed_at', 'revoked_at', 'replaced_by_id',
    ];

    public function session()
    {
        return $this->belongsTo(AuthSession::class, 'auth_session_id');
    }

    public function replacedBy()
    {
        return $this->belongsTo(AuthRefreshToken::class, 'replaced_by_id');
    }

    public function isActive(): bool
    {
        return $this->state === RefreshTokenState::ACTIVE;
    }
}
