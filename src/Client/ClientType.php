<?php

declare(strict_types=1);

namespace Fschmtt\Keycloak\Client;

use Fschmtt\Keycloak\Enum\Enum;

enum ClientType: string implements Enum
{
    case ADMIN = 'admin';
    case CONFIDENTIAL = 'confidential';
}
