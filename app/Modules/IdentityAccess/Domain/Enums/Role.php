<?php

namespace App\Modules\IdentityAccess\Domain\Enums;

enum Role: string
{
    case ADMINISTRADOR_PROPIETARIO = 'ADMINISTRADOR_PROPIETARIO';
    case OPERADOR = 'OPERADOR';
}
