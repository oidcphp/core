<?php

namespace OpenIDConnect\Metadata;

use ArrayAccess;
use JsonSerializable;
use OpenIDConnect\Metadata\MetadataAwareTraits;

/**
 * The metadata of client registration
 *
 * @see https://tools.ietf.org/html/rfc6749#section-2
 */
class ClientMetadata implements ArrayAccess, JsonSerializable
{
    use MetadataAwareTraits;

    public const REQUIRED_METADATA = [
        'client_id',
        'client_secret',
        'redirect_uri',
    ];

    /**
     * @param array $metadata
     */
    public function __construct(array $metadata = [])
    {
        $this->metadata = $metadata;

        $this->assertKeys(self::REQUIRED_METADATA);
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return $this->metadata['client_id'];
    }

    /**
     * {@inheritDoc}
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @return string
     */
    public function redirectUri(): string
    {
        return $this->metadata['redirect_uri'];
    }

    /**
     * @return string
     */
    public function secret(): string
    {
        return $this->metadata['client_secret'];
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->metadata;
    }
}
