<?php

namespace OpenIDConnect\Core;

use OpenIDConnect\Jwt\JwtFactory;
use OpenIDConnect\Token\TokenSet;
use OpenIDConnect\Token\TokenSetInterface;

/**
 * OpenID Connect addition method
 */
trait Client
{
    /**
     * @var null|string
     */
    private $nonce;

    /**
     * @return null|string
     */
    public function getNonce(): ?string
    {
        return $this->nonce;
    }

    /**
     * @param array $parameters
     * @param array $checks
     * @return TokenSetInterface
     */
    public function handleOpenIDConnectCallback(array $parameters, array $checks = []): TokenSetInterface
    {
        /** @var TokenSet $tokenSet */
        $tokenSet = $this->handleCallback($parameters, $checks);

        $tokenSet->setClientInformation($this->clientInformation);
        $tokenSet->setProviderMetadata($this->providerMetadata);
        $tokenSet->setJwtFactory(new JwtFactory($this->providerMetadata, $this->clientInformation));

        return $tokenSet;
    }
}
