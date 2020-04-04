<?php

namespace OpenIDConnect\Contracts;

interface ClientMetadataInterface extends Parameterable
{
    /**
     * OAuth 2.0 client identifier string.
     *
     * @return string
     */
    public function id(): string;

    /**
     * OAuth 2.0 client secret string.
     *
     * PHP Server will be a confidential client, so secret is REQUIRED.
     *
     * @return string
     */
    public function secret(): string;
}
