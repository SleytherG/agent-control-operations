<?php

namespace App\Modules\IdentityAccess\Domain\Enums;

enum RefreshTokenState: string
{
    case ACTIVE = 'ACTIVE';
    case CONSUMED = 'CONSUMED';
    case REVOKED = 'REVOKED';
}
