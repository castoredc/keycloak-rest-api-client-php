<?php

declare(strict_types=1);

namespace Fschmtt\Keycloak\Client;

use Fschmtt\Keycloak\Keycloak;
use Fschmtt\Keycloak\OAuth\TokenStorageInterface;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;

final class ConfidentialClient extends ClientBase
{
    public function __construct(
        private readonly Keycloak              $keycloak,
        private readonly GuzzleClientInterface $httpClient,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly string                $realmName = 'master'
    ) {
        parent::__construct($this->keycloak, $this->httpClient, $this->tokenStorage);
    }

    protected function getEndpointUrl(): string
    {
        return $this->keycloak->getBaseUrl() . sprintf('/realms/%s/protocol/openid-connect/token', $this->realmName);
    }

    /**
     * @return array{client_id: string, client_secret: string, grant_type: string}
     */
    protected function getAccessTokenFormParameters(): array
    {
        return [
            'client_id' => $this->keycloak->getUsername(),
            'client_secret' => $this->keycloak->getPassword(),
            'grant_type' => 'client_credentials',
        ];
    }

    /**
     * @return array{refresh_token: string, client_id: string, grant_type: string}
     */
    protected function getRefreshTokenFormParameters(): array
    {
        return [
            'refresh_token' => $this->tokenStorage->retrieveRefreshToken()?->toString(),
            'client_id' => $this->keycloak->getUsername(),
            'grant_type' => 'refresh_token',
        ];
    }
}
