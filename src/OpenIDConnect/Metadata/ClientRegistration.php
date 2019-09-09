<?php

declare(strict_types=1);

namespace OpenIDConnect\Metadata;

use ArrayAccess;
use JsonSerializable;

/**
 * Client registration info
 *
 * @see https://tools.ietf.org/html/rfc6749#section-2
 */
class ClientRegistration implements ArrayAccess, JsonSerializable
{
    use MetadataTraits;

    public const REQUIRED = [
        'client_id',
        'client_secret',
        'redirect_uris',
    ];

    private $redirectUri;

    /**
     * @param array $metadata
     * @param string $redirectUri
     */
    public function __construct(array $metadata, string $redirectUri = null)
    {
        $this->metadata = $metadata;
        $this->assertKeys(self::REQUIRED);

        $this->redirectUri = $redirectUri;
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return $this->metadata['client_id'];
    }

    /**
     * @return string
     */
    public function redirectUri(): string
    {
        return $this->redirectUri;
    }

    /**
     * @return array
     */
    public function redirectUris(): array
    {
        return $this->metadata['redirect_uris'];
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

    /**
     * @param string $redirectUri
     * @return static
     */
    public function withRedirectUri(string $redirectUri)
    {
        $this->redirectUri = $redirectUri;

        return $this;
    }
}
