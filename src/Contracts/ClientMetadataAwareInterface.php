<?php

namespace OpenIDConnect\Contracts;

interface ClientMetadataAwareInterface
{
    /**
     * @return ClientMetadataInterface
     */
    public function clientMetadata(): ClientMetadataInterface;

    /**
     * @param ClientMetadataInterface $clientMetadata
     * @return $this
     */
    public function setClientMetadata(ClientMetadataInterface $clientMetadata);
}
