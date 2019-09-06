<?php

namespace OpenIDConnect\Metadata;

use OpenIDConnect\Jwt\JwtFactory;

trait MetadataAwareTraits
{
    /**
     * @var ClientMetadata
     */
    private $clientMetadata;

    /**
     * @var ProviderMetadata
     */
    private $providerMetadata;

    /**
     * @return JwtFactory
     */
    public function createJwtFactory(): JwtFactory
    {
        return $this->providerMetadata->createJwtFactory($this->clientMetadata);
    }

    /**
     * @return ClientMetadata
     */
    public function getClientMetadata(): ClientMetadata
    {
        return $this->clientMetadata;
    }

    /**
     * @return ProviderMetadata
     */
    public function getProviderMetadata(): ProviderMetadata
    {
        return $this->providerMetadata;
    }

    /**
     * @param ClientMetadata $clientMetadata
     * @return static
     */
    public function setClientMetadata(ClientMetadata $clientMetadata)
    {
        $this->clientMetadata = $clientMetadata;
        return $this;
    }

    /**
     * @param ProviderMetadata $providerMetadata
     * @return static
     */
    public function setProviderMetadata(ProviderMetadata $providerMetadata)
    {
        $this->providerMetadata = $providerMetadata;
        return $this;
    }
}
