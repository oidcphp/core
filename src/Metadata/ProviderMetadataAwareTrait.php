<?php

declare(strict_types=1);

namespace OpenIDConnect\Metadata;

use OpenIDConnect\Contracts\ProviderMetadataInterface;

trait ProviderMetadataAwareTrait
{
    /**
     * @var ProviderMetadataInterface
     */
    protected $providerMetadata;

    /**
     * @return ProviderMetadataInterface
     */
    public function providerMetadata(): ProviderMetadataInterface
    {
        return $this->providerMetadata;
    }

    /**
     * @param ProviderMetadataInterface $providerMetadata
     * @return $this
     */
    public function setProviderMetadata(ProviderMetadataInterface $providerMetadata)
    {
        $this->providerMetadata = $providerMetadata;

        return $this;
    }
}
