<?php

declare(strict_types=1);

namespace OpenIDConnect\OAuth2\Metadata;

use JsonSerializable;
use OpenIDConnect\OAuth2\Traits\ParameterTrait;

/**
 * Client metadata
 *
 * @see https://tools.ietf.org/html/rfc7591#section-2
 */
class ClientMetadata implements JsonSerializable
{
    use ParameterTrait;

    /**
     * @param array<mixed> $metadata
     */
    public function __construct(array $metadata = [])
    {
        $this->parameters = $metadata;
    }
}
