<?php

namespace App\Modules\IdentityAccess\Domain\Enums;

enum AuthSessionStatus: string
{
    case ACTIVE = 'ACTIVE';
    case EXPIRED = 'EXPIRED';
    case REVOKED = 'REVOKED';
}
