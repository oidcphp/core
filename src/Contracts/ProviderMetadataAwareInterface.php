<?php

namespace OpenIDConnect\Contracts;

use DomainException;

interface ProviderMetadataAwareInterface
{
    /**
     * @return ProviderMetadataInterface
     */
    public function providerMetadata(): ProviderMetadataInterface;

    /**
     * Get provider metadata by key
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getProviderMetadata(string $name, $default = null);

    /**
     * Require provider metadata, throw exception when not found
     *
     * @param string $name
     * @return mixed
     * @throws DomainException
     */
    public function requireProviderMetadata(string $name);

    /**
     * @param ProviderMetadataInterface $providerMetadata
     * @return $this
     */
    public function setProviderMetadata(ProviderMetadataInterface $providerMetadata);
}
