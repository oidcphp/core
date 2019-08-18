<?php

namespace OpenIDConnect;

use InvalidArgumentException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use OpenIDConnect\Metadata\ClientMetadata;
use OpenIDConnect\Metadata\ProviderMetadata;
use OpenIDConnect\Token\TokenSet;
use OpenIDConnect\Token\TokenSetInterface;
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
     * @param string $redirectUri
     * @param array $parameters
     * @param array $checks
     * @return TokenSetInterface
     * @throws IdentityProviderException
     */
    public function handleCallback(string $redirectUri, array $parameters, array $checks = [])
    {
        if (isset($parameters['state']) && !isset($checks['state'])) {
            throw new InvalidArgumentException("'state' argument is missing");
        }

        if (!isset($parameters['state']) && isset($checks['state'])) {
            throw new InvalidArgumentException("'state' missing from the response");
        }

        if (isset($parameters['state'], $checks['state']) && ($checks['state'] !== $parameters['state'])) {
            throw new UnexpectedValueException(sprintf(
                'State mismatch, expected %s, got: %s',
                $checks['state'],
                $parameters['state']
            ));
        }

        return $this->getTokenSet('authorization_code', [
            'code' => $parameters['code'],
            'redirect_uri' => $redirectUri,
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
    protected function getTokenSet($grant, array $options = []): TokenSetInterface
    {
        $grant = $this->verifyGrant($grant);

        $params = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
        ];

        $params = $grant->prepareRequestParameters($params, $options);
        $request = $this->getAccessTokenRequest($params);
        $response = $this->getParsedResponse($request);

        if (!is_array($response)) {
            throw new UnexpectedValueException(
                'Invalid response received from Authorization Server. Expected JSON.'
            );
        }

        return new TokenSet($response);
    }
}
