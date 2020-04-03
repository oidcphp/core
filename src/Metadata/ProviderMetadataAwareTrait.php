<?php

declare(strict_types=1);

namespace OpenIDConnect\Metadata;

trait ProviderMetadataAwareTrait
{
    /**
     * @var ProviderMetadata
     */
    protected $providerMetadata;

    /**
     * @param ProviderMetadata $providerMetadata
     * @return $this
     */
    public function setProviderMetadata(ProviderMetadata $providerMetadata): self
    {
        $this->providerMetadata = $providerMetadata;

        return $this;
    }
}
