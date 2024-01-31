<?php

declare(strict_types=1);

namespace Fschmtt\Keycloak\Client;

use Fschmtt\Keycloak\Keycloak;
use Fschmtt\Keycloak\OAuth\TokenStorageInterface;
use GuzzleHttp\Client as GuzzleClient;

final class ClientFactory
{
    public static function create(
        Keycloak              $keycloak,
        TokenStorageInterface $tokenStorage,
        ClientType            $clientType,
        string                $realmName = 'master'
    ): ClientInterface {
        return match ($clientType) {
            ClientType::ADMIN => new AdminClient($keycloak, new GuzzleClient(), $tokenStorage),
            ClientType::CONFIDENTIAL => new ConfidentialClient($keycloak, new GuzzleClient(), $tokenStorage, $realmName),
        };
    }
}
