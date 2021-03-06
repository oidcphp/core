<?php

declare(strict_types=1);

namespace OpenIDConnect\Metadata;

use JsonSerializable;
use OpenIDConnect\Traits\ParameterTrait;

/**
 * Client metadata
 *
 * @see https://tools.ietf.org/html/rfc7591#section-2
 * @see https://tools.ietf.org/html/rfc7591#section-3.2.1
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

    /**
     * OAuth 2.0 client identifier string.
     *
     * @return string
     */
    public function id(): string
    {
        return $this->get('client_id');
    }

    /**
     * Time at which the client identifier was issued.
     *
     * @return int|null
     */
    public function issuedAt(): ?int
    {
        return $this->get('client_id_issued_at');
    }

    /**
     * OAuth 2.0 client secret string.
     *
     * PHP Server will be a confidential client, so secret is REQUIRED.
     *
     * @return string
     */
    public function secret(): string
    {
        return $this->get('client_secret');
    }

    /**
     * Time at which the client secret will expire or 0 if it will not expire.
     *
     * @return int|null
     */
    public function expiresAt(): ?int
    {
        return $this->get('client_secret_expires_at');
    }
}
