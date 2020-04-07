<?php

namespace OpenIDConnect\Contracts;

use DomainException;

interface ClientMetadataAwareInterface
{
    /**
     * @return ClientMetadataInterface
     */
    public function clientMetadata(): ClientMetadataInterface;

    /**
     * Get Client metadata by key
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getClientMetadata(string $name, $default = null);

    /**
     * Require client metadata, throw exception when not found
     *
     * @param string $name
     * @return mixed
     * @throws DomainException
     */
    public function requireClientMetadata(string $name);

    /**
     * @param ClientMetadataInterface $clientMetadata
     * @return $this
     */
    public function setClientMetadata(ClientMetadataInterface $clientMetadata);
}
