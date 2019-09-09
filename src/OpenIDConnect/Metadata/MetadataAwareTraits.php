<?php

namespace OpenIDConnect\Metadata;

use OpenIDConnect\Jwt\JwtFactory;

trait MetadataAwareTraits
{
    /**
     * @var ClientRegistration
     */
    private $clientRegistration;

    /**
     * @var ProviderMetadata
     */
    private $providerMetadata;

    /**
     * @return JwtFactory
     */
    public function createJwtFactory(): JwtFactory
    {
        return $this->providerMetadata->createJwtFactory($this->clientRegistration);
    }

    /**
     * @return ClientRegistration
     */
    public function getClientRegistration(): ClientRegistration
    {
        return $this->clientRegistration;
    }

    /**
     * @return ProviderMetadata
     */
    public function getProviderMetadata(): ProviderMetadata
    {
        return $this->providerMetadata;
    }

    /**
     * @param ClientRegistration $clientRegistration
     * @return static
     */
    public function setClientRegistration(ClientRegistration $clientRegistration)
    {
        $this->clientRegistration = $clientRegistration;

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
