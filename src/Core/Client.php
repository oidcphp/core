<?php

namespace OpenIDConnect\Core;

use OpenIDConnect\Core\Jwt\JwtFactory;
use OpenIDConnect\Core\Token\TokenSet;
use OpenIDConnect\OAuth2\Token\TokenSetInterface;

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
