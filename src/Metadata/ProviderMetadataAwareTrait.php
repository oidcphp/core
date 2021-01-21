<?php

declare(strict_types=1);

namespace OpenIDConnect\Metadata;

trait ProviderMetadataAwareTrait
{
    /**
     * @var ProviderMetadata
     */
    protected $providerMetadata;

    public function providerMetadata(): ProviderMetadata
    {
        return $this->providerMetadata;
    }

    public function getProviderMetadata(string $name, $default = null)
    {
        return $this->providerMetadata->get($name, $default);
    }

    public function requireProviderMetadata(string $name)
    {
        return $this->providerMetadata->require($name);
    }

    public function setProviderMetadata(ProviderMetadata $providerMetadata)
    {
        $this->providerMetadata = $providerMetadata;

        return $this;
    }
}
