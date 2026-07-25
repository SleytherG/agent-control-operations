<?php

namespace App\Modules\IdentityAccess\Domain\Enums;

enum PasswordResetStatus: string
{
    case PENDING = 'PENDING';
    case CONSUMED = 'CONSUMED';
    case COMPLETED = 'COMPLETED';
    case SUPERSEDED = 'SUPERSEDED';
    case EXPIRED = 'EXPIRED';

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::PENDING => in_array($next, [self::CONSUMED, self::SUPERSEDED, self::EXPIRED], true),
            self::CONSUMED => in_array($next, [self::COMPLETED, self::SUPERSEDED], true),
            default => false,
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::COMPLETED, self::SUPERSEDED, self::EXPIRED], true);
    }
}
