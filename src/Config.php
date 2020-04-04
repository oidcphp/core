<?php

namespace OpenIDConnect;

use OpenIDConnect\Contracts\ClientMetadataInterface;
use OpenIDConnect\Contracts\ConfigInterface;
use OpenIDConnect\Contracts\ProviderMetadataInterface;
use OpenIDConnect\Metadata\ClientMetadataAwareTrait;
use OpenIDConnect\Metadata\ProviderMetadataAwareTrait;

class Config implements ConfigInterface
{
    use ClientMetadataAwareTrait;
    use ProviderMetadataAwareTrait;

    public function __construct(ProviderMetadataInterface $providerMetadata, ClientMetadataInterface $clientMetadata)
    {
        $this->setClientMetadata($clientMetadata);
        $this->setProviderMetadata($providerMetadata);
    }
}
