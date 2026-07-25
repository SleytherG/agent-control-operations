<?php

namespace App\Modules\IdentityAccess\Domain\Enums;

enum SessionEventType: string
{
    case LOGIN = 'LOGIN';
    case REFRESHED = 'REFRESHED';
    case LOGOUT = 'LOGOUT';
    case EXPIRED = 'EXPIRED';
    case ADMIN_REVOKED = 'ADMIN_REVOKED';
    case PASSWORD_RESET_REVOKED = 'PASSWORD_RESET_REVOKED';
    case PASSWORD_RESET_LOGIN = 'PASSWORD_RESET_LOGIN';
    case REFRESH_REUSE = 'REFRESH_REUSE';
    case LOGIN_FAILED = 'LOGIN_FAILED';
}
