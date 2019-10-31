<?php

namespace OpenIDConnect\Core\Metadata;

use ArrayAccess;
use JsonSerializable;

/**
 * The client metadata
 *
 * @see https://openid.net/specs/openid-connect-registration-1_0.html#ClientMetadata
 */
class ClientMetadata implements ArrayAccess, JsonSerializable
{
    use MetadataTraits;

    public const REQUIRED = [
        'redirect_uris',
    ];

    /**
     * @param array $metadata
     */
    public function __construct(array $metadata = [])
    {
        $this->metadata = $metadata;

        $this->assertKeys(self::REQUIRED);
    }

    /**
     * @return array
     */
    public function contacts(): array
    {
        return $this->metadata['contacts'] ?? [];
    }

    /**
     * @return array
     */
    public function grantTypes(): array
    {
        return $this->metadata['grant_types'] ?? [];
    }

    /**
     * @return array
     */
    public function redirectUris(): array
    {
        return $this->metadata['redirect_uris'];
    }

    /**
     * @return array
     */
    public function responseTypes(): array
    {
        return $this->metadata['response_types'] ?? [];
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->metadata;
    }
}
