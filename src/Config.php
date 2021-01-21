<?php

namespace OpenIDConnect;

use OpenIDConnect\Metadata\ClientMetadata;
use OpenIDConnect\Metadata\ClientMetadataAwareTrait;
use OpenIDConnect\Metadata\ProviderMetadata;
use OpenIDConnect\Metadata\ProviderMetadataAwareTrait;

class Config
{
    use ClientMetadataAwareTrait;
    use ProviderMetadataAwareTrait;

    public function __construct(ProviderMetadata $providerMetadata, ClientMetadata $clientMetadata)
    {
        $this->setClientMetadata($clientMetadata);
        $this->setProviderMetadata($providerMetadata);
    }
}
