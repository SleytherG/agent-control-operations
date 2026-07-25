<?php

namespace App\Modules\IdentityAccess\Domain\Enums;

enum SessionEndReason: string
{
    case LOGOUT_MANUAL = 'LOGOUT_MANUAL';
    case EXPIRACION = 'EXPIRACION';
    case REVOCACION_ADMINISTRATIVA = 'REVOCACION_ADMINISTRATIVA';
    case PASSWORD_RESET = 'PASSWORD_RESET';
    case FALLO_SEGURIDAD = 'FALLO_SEGURIDAD';
}
