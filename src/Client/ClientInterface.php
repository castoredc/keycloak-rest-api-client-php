<?php

declare(strict_types=1);

namespace Fschmtt\Keycloak\Client;

use Psr\Http\Message\ResponseInterface;

interface ClientInterface
{
    /**
     * @param array<string, mixed> $options
     */
    public function request(string $method, string $path = '', array $options = []): ResponseInterface;

    public function isAuthorized(): bool;
}
