<?php

declare(strict_types=1);

namespace OpenIDConnect\OAuth2\ClientAuthentication;

trait ClientAuthenticationAwareTrait
{
    /**
     * @var ClientAuthentication|null
     */
    protected $clientAuthentication;

    /**
     * Default method for client authentication is ClientSecretBasic
     *
     * @param string $clientId
     * @param string $clientSecret
     * @return ClientAuthentication
     */
    public function resolveClientAuthenticationByDefault(string $clientId, string $clientSecret): ClientAuthentication
    {
        if (null === $this->clientAuthentication) {
            return new ClientSecretBasic($clientId, $clientSecret);
        }

        return $this->clientAuthentication;
    }

    /**
     * @param ClientAuthentication|null $clientAuthentication
     * @return static
     */
    public function setClientAuthentication(?ClientAuthentication $clientAuthentication)
    {
        $this->clientAuthentication = $clientAuthentication;

        return $this;
    }
}
