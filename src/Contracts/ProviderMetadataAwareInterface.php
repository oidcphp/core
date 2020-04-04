<?php

namespace OpenIDConnect\Contracts;

interface ProviderMetadataAwareInterface
{
    /**
     * @return ProviderMetadataInterface
     */
    public function providerMetadata(): ProviderMetadataInterface;

    /**
     * @param ProviderMetadataInterface $providerMetadata
     * @return $this
     */
    public function setProviderMetadata(ProviderMetadataInterface $providerMetadata);
}
