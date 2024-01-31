<?php

declare(strict_types=1);

namespace Fschmtt\Keycloak\Http;

use Fschmtt\Keycloak\Client\ClientInterface;
use Fschmtt\Keycloak\Json\JsonDecoder;
use Fschmtt\Keycloak\Serializer\Serializer;

class QueryExecutor
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly Serializer $serializer
    ) {
    }

    public function executeQuery(Query $query): mixed
    {
        $response = $this->client->request(
            $query->getMethod()->value,
            $query->getPath(),
        );

        return $this->serializer->serialize(
            $query->getReturnType(),
            (new JsonDecoder())->decode($response->getBody()->getContents())
        );
    }
}
