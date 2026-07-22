<?php

namespace App\Modules\IdentityAccess\Domain\Enums;

enum UserStatus: string
{
    case ACTIVE = 'ACTIVE';
    case INACTIVE = 'INACTIVE';
}
