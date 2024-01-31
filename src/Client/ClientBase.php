<?php

declare(strict_types=1);

namespace Fschmtt\Keycloak\Client;

use DateTime;
use Fschmtt\Keycloak\Keycloak;
use Fschmtt\Keycloak\OAuth\TokenStorageInterface;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\ClientException;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token;
use Psr\Http\Message\ResponseInterface;

abstract class ClientBase implements ClientInterface
{
    public function __construct(
        private readonly Keycloak              $keycloak,
        private readonly GuzzleClientInterface $httpClient,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function request(string $method, string $path = '', array $options = []): ResponseInterface
    {
        if (!$this->isAuthorized()) {
            $this->authorize();
        }

        $defaultOptions = [
            'base_uri' => $this->keycloak->getBaseUrl(),
            'headers' => [
                'Authorization' => 'Bearer ' . $this->tokenStorage->retrieveAccessToken()->toString(),
            ],
        ];

        $options = array_merge_recursive($options, $defaultOptions);

        return $this->httpClient->request(
            $method,
            $this->keycloak->getBaseUrl() . $path,
            $options
        );
    }

    public function isAuthorized(): bool
    {
        return $this->tokenStorage->retrieveAccessToken() !== null && !$this->tokenStorage->retrieveAccessToken()->isExpired(new DateTime());
    }

    private function authorize(): void
    {
        $tokens = $this->fetchTokens();
        $parser = (new Token\Parser(new JoseEncoder()));

        $this->tokenStorage->storeAccessToken($parser->parse($tokens['access_token']));

        if (is_string($tokens['refresh_token'])) {
            $this->tokenStorage->storeRefreshToken($parser->parse($tokens['refresh_token']));
        }
    }

    /**
     * @return array{access_token: string, refresh_token: string}
     */
    protected function fetchTokens(): array
    {
        $endpointUrl = $this->getEndpointUrl();

        try {
            $response = $this->httpClient->request(
                'POST',
                $endpointUrl,
                [
                    'form_params' => $this->getAccessTokenFormParameters(),
                ],
            );
        } catch (ClientException $e) {
            $formParams = $this->getRefreshTokenFormParameters();
            $response = $this->httpClient->request(
                'POST',
                $endpointUrl,
                [
                    'form_params' => $formParams,
                ],
            );
        }

        $tokens = json_decode($response->getBody()->getContents(), true, flags: JSON_THROW_ON_ERROR);

        return [
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'],
        ];
    }

    abstract protected function getEndpointUrl(): string;

    /**
     * @return array<mixed>
     */
    abstract protected function getAccessTokenFormParameters(): array;

    /**
     * @return array<mixed>
     */
    abstract protected function getRefreshTokenFormParameters(): array;
}
