<?php

namespace OpenIDConnect;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use OpenIDConnect\Metadata\ClientMetadata;
use OpenIDConnect\Metadata\ProviderMetadata;
use Psr\Http\Message\ResponseInterface;
use UnexpectedValueException;

/**
 * OpenID Connect Client
 */
class Client extends AbstractProvider
{
    /**
     * @var ProviderMetadata
     */
    private $providerMetadata;

    /**
     * @var ClientMetadata
     */
    private $clientMetadata;

    /**
     * @param ProviderMetadata $providerMetadata
     * @param ClientMetadata $clientMetadata
     * @param array $collaborators
     */
    public function __construct(ProviderMetadata $providerMetadata, ClientMetadata $clientMetadata, $collaborators = [])
    {
        $this->providerMetadata = $providerMetadata;
        $this->clientMetadata = $clientMetadata;

        parent::__construct([
            'clientId' => $clientMetadata->id(),
            'clientSecret' => $clientMetadata->secret(),
            'redirectUri' => $clientMetadata->redirectUri(),
        ], $collaborators);
    }

    /**
     * @param array $options
     * @return string
     */
    public function getAuthorizationPost(array $options = []): string
    {
        $baseAuthorizationUrl = $this->getBaseAuthorizationUrl();

        $parameters = $this->getAuthorizationParameters($options);

        $formInput = implode('', array_map(function ($v, $k) {
            return "<input type=\"hidden\" name=\"${k}\" value=\"${v}\"/>";
        }, $parameters, array_keys($parameters)));

        return <<< HTML
<!DOCTYPE html>
<head><title>Requesting Authorization</title></head>
<body onload="javascript:document.forms[0].submit()">
<form method="post" action="${baseAuthorizationUrl}">${formInput}</form>
</body>
</html>
HTML;
    }

    /**
     * {@inheritDoc}
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->providerMetadata->authorizationEndpoint();
    }

    /**
     * {@inheritDoc}
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->providerMetadata->tokenEndpoint();
    }

    /**
     * {@inheritDoc}
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->providerMetadata['userinfo_endpoint'];
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultScopes()
    {
        return ['openid'];
    }

    /**
     * {@inheritDoc}
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
    }

    /**
     * {@inheritDoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        throw new \LogicException('Not implement');
    }
}
