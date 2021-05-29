<?php

declare(strict_types=1);

namespace Fschmtt\Keycloak\Serializer;

interface SerializerInterface
{
    public function serializes(): string;

    public function serialize($value): mixed;
}
