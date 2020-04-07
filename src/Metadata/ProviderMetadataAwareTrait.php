<?php

declare(strict_types=1);

namespace OpenIDConnect\Metadata;

use OpenIDConnect\Contracts\ProviderMetadataInterface;

/**
 * @see \OpenIDConnect\Contracts\ProviderMetadataAwareInterface
 */
trait ProviderMetadataAwareTrait
{
    /**
     * @var ProviderMetadataInterface
     */
    protected $providerMetadata;

    public function providerMetadata(): ProviderMetadataInterface
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

    public function setProviderMetadata(ProviderMetadataInterface $providerMetadata)
    {
        $this->providerMetadata = $providerMetadata;

        return $this;
    }
}
