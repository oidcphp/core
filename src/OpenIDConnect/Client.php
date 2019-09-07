<?php

namespace OpenIDConnect;

use InvalidArgumentException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use OpenIDConnect\Exceptions\OpenIDProviderException;
use OpenIDConnect\Exceptions\RelyingPartyException;
use OpenIDConnect\Http\TokenRequestFactory;
use OpenIDConnect\Metadata\ClientMetadata;
use OpenIDConnect\Metadata\MetadataAwareTraits;
use OpenIDConnect\Metadata\ProviderMetadata;
use OpenIDConnect\Token\TokenSet;
use OpenIDConnect\Token\TokenSetInterface;
use OpenIDConnect\Traits\ClientAuthenticationAwareTrait;
use Psr\Http\Message\ResponseInterface;

/**
 * OpenID Connect Client
 */
class Client extends AbstractProvider
{
    use ClientAuthenticationAwareTrait;
    use MetadataAwareTraits;

    /**
     * @param ProviderMetadata $providerMetadata
     * @param ClientMetadata $clientMetadata
     * @param array $collaborators
     */
    public function __construct(ProviderMetadata $providerMetadata, ClientMetadata $clientMetadata, $collaborators = [])
    {
        $this->setProviderMetadata($providerMetadata);
        $this->setClientMetadata($clientMetadata);

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
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->providerMetadata->tokenEndpoint();
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
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->providerMetadata->userInfoEndpoint() ?? '';
    }

    /**
     * @param array $parameters
     * @param array $checks
     * @return TokenSetInterface
     * @throws IdentityProviderException
     */
    public function handleOpenIDConnectCallback(array $parameters, array $checks = [])
    {
        if (isset($parameters['state']) && !isset($checks['state'])) {
            throw new InvalidArgumentException("'state' argument is missing");
        }

        if (!isset($parameters['state']) && isset($checks['state'])) {
            throw new RelyingPartyException("'state' missing from the response");
        }

        if (isset($parameters['state'], $checks['state']) && ($checks['state'] !== $parameters['state'])) {
            throw new RelyingPartyException(sprintf(
                'State mismatch, expected %s, got: %s',
                $checks['state'],
                $parameters['state']
            ));
        }

        return $this->getTokenSet('authorization_code', [
            'code' => $parameters['code'],
            'redirect_uri' => $this->clientMetadata->redirectUri(),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (is_array($data) && !empty($data['error'])) {
            $error = $data['error'];

            throw new IdentityProviderException($error, 0, $data);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        throw new \LogicException('Not implement');
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultScopes()
    {
        return ['openid'];
    }

    /**
     * @param mixed $grant
     * @param array $options
     * @return TokenSetInterface
     * @throws IdentityProviderException
     */
    private function getTokenSet($grant, array $options = []): TokenSetInterface
    {
        $grant = $this->verifyGrant($grant);

        $params = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
        ];

        $params = $grant->prepareRequestParameters($params, $options);

        $request = (new TokenRequestFactory($this->providerMetadata->tokenEndpoint()))
            ->createRequest($params);

        $appender = $this->getTokenRequestAppender();
        $appendedRequest = $appender->withClientAuthentication(
            $request,
            $this->clientMetadata->id(),
            $this->clientMetadata->secret()
        );

        $response = $this->getParsedResponse($appendedRequest);

        if (!is_array($response)) {
            throw new OpenIDProviderException(
                'Invalid response received from OpenID Provider. Expected JSON.'
            );
        }

        $tokenSet = new TokenSet($response, $this->providerMetadata, $this->clientMetadata);

        if (!$tokenSet->hasIdToken()) {
            throw new OpenIDProviderException("'id_token' missing from the token endpoint response");
        }

        return $tokenSet;
    }
}
