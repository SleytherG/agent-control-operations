<?php

namespace App\Modules\IdentityAccess\Models;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    protected $casts = [
        'deactivated_at' => 'datetime',
        'role' => Role::class,
        'status' => UserStatus::class,
    ];

    protected $fillable = [
        'public_id', 'organization_id', 'username_normalized', 'email_normalized',
        'password', 'role', 'status', 'deactivated_at', 'deactivated_by',
        'deactivation_reason',
    ];

    protected $hidden = ['password'];

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            $user->public_id ??= (string) Str::uuid();
        });
    }

    public function sessions()
    {
        return $this->hasMany(AuthSession::class);
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::ACTIVE;
    }

    public function isAdministradorPropietario(): bool
    {
        return $this->role === Role::ADMINISTRADOR_PROPIETARIO;
    }

    public function normalizeIdentifier(string $value): string
    {
        return Str::lower(trim($value));
    }
}
