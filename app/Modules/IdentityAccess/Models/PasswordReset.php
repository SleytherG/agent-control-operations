<?php

namespace App\Modules\IdentityAccess\Models;

use App\Modules\IdentityAccess\Domain\Enums\PasswordResetStatus;
use App\Modules\Organization\Models\Organization;
use Database\Factories\IdentityAccess\PasswordResetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PasswordReset extends Model
{
    /** @use HasFactory<PasswordResetFactory> */
    use HasFactory;

    protected $fillable = [
        'public_id', 'organization_id', 'user_id', 'initiated_by_user_id', 'status',
        'issued_at', 'expires_at', 'consumed_at', 'completed_at', 'superseded_at', 'reason',
    ];

    protected $casts = [
        'status' => PasswordResetStatus::class,
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
        'consumed_at' => 'datetime',
        'completed_at' => 'datetime',
        'superseded_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (PasswordReset $reset) {
            $reset->public_id ??= (string) Str::uuid();
        });
    }

    protected static function newFactory(): PasswordResetFactory
    {
        return PasswordResetFactory::new();
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function initiatedBy()
    {
        return $this->belongsTo(User::class, 'initiated_by_user_id');
    }

    public function restrictedSession()
    {
        return $this->hasOne(AuthSession::class);
    }
}
