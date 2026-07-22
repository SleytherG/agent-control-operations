<?php

namespace App\Modules\IdentityAccess\Domain\Enums;

enum SessionEventType: string
{
    case LOGIN = 'LOGIN';
    case REFRESHED = 'REFRESHED';
    case LOGOUT = 'LOGOUT';
    case EXPIRED = 'EXPIRED';
    case ADMIN_REVOKED = 'ADMIN_REVOKED';
    case REFRESH_REUSE = 'REFRESH_REUSE';
    case LOGIN_FAILED = 'LOGIN_FAILED';
}
