<?php

declare(strict_types=1);

namespace Fschmtt\Keycloak\Client;

use Fschmtt\Keycloak\Keycloak;
use Fschmtt\Keycloak\OAuth\TokenStorageInterface;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;

class AdminClient extends ClientBase
{
    public function __construct(
        private readonly Keycloak              $keycloak,
        private readonly GuzzleClientInterface $httpClient,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
        parent::__construct($this->keycloak, $this->httpClient, $this->tokenStorage);
    }

    protected function getEndpointUrl(): string
    {
        return $this->keycloak->getBaseUrl() . '/realms/master/protocol/openid-connect/token';
    }

    /**
     * @return array{username: string, password: string, client_id: string, grant_type: string}
     */
    protected function getAccessTokenFormParameters(): array
    {
        return [
            'username' => $this->keycloak->getUsername(),
            'password' => $this->keycloak->getPassword(),
            'client_id' => 'admin-cli',
            'grant_type' => 'password',
        ];
    }

    /**
     * @return array{refresh_token: string, client_id: string, grant_type: string}
     */
    protected function getRefreshTokenFormParameters(): array
    {
        return [
            'refresh_token' => $this->tokenStorage->retrieveRefreshToken()?->toString(),
            'client_id' => 'admin-cli',
            'grant_type' => 'refresh_token',
        ];
    }
}
