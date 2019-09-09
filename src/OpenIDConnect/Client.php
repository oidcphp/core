<?php

namespace OpenIDConnect;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\ClientInterface as HttpClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use InvalidArgumentException;
use OpenIDConnect\Container\Container;
use OpenIDConnect\Exceptions\OpenIDProviderException;
use OpenIDConnect\Exceptions\RelyingPartyException;
use OpenIDConnect\Http\DefaultUriFactory;
use OpenIDConnect\Http\QueryProcessorTrait;
use OpenIDConnect\Http\TokenRequestFactory;
use OpenIDConnect\Metadata\ClientMetadata;
use OpenIDConnect\Metadata\MetadataAwareTraits;
use OpenIDConnect\Metadata\ProviderMetadata;
use OpenIDConnect\OAuth2\Grant\GrantFactory;
use OpenIDConnect\Token\TokenSet;
use OpenIDConnect\Token\TokenSetInterface;
use OpenIDConnect\Traits\ClientAuthenticationAwareTrait;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\UriInterface;

/**
 * OpenID Connect Client
 */
class Client
{
    use ClientAuthenticationAwareTrait;
    use MetadataAwareTraits;
    use QueryProcessorTrait;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $state;

    /**
     * @param ProviderMetadata $providerMetadata
     * @param ClientMetadata $clientMetadata
     * @param ContainerInterface|null $container The container implements PSR-11
     */
    public function __construct(
        ProviderMetadata $providerMetadata,
        ClientMetadata $clientMetadata,
        ContainerInterface $container = null
    ) {
        $this->setProviderMetadata($providerMetadata);
        $this->setClientMetadata($clientMetadata);

        if (null === $container) {
            $container = new Container([
                GrantFactory::class => new GrantFactory(),
                HttpClientInterface::class => new HttpClient(),
            ]);
        }

        $this->container = $container;
    }

    /**
     * @param array $options
     * @return UriInterface
     */
    public function getAuthorizationUri(array $options = []): UriInterface
    {
        $params = $this->getAuthorizationParameters($options);

        return (new DefaultUriFactory())
            ->createUri($this->providerMetadata->authorizationEndpoint())
            ->withQuery($this->buildQueryString($params));
    }

    /**
     * @param array $options
     * @return string
     */
    public function getAuthorizationPost(array $options = []): string
    {
        $baseAuthorizationUrl = $this->providerMetadata->authorizationEndpoint();

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

    protected function getRandomState($length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    protected function getAuthorizationParameters(array $options): array
    {
        if (empty($options['state'])) {
            $options['state'] = $this->getRandomState();
        }

        if (empty($options['scope'])) {
            $options['scope'] = ['openid'];
        }

        $options += [
            'response_type' => 'code',
        ];

        if (is_array($options['scope'])) {
            $options['scope'] = implode(' ', $options['scope']);
        }

        // Store the state as it may need to be accessed later on.
        $this->state = $options['state'];

        // Business code layer might set a different redirect_uri parameter
        // depending on the context, leave it as-is
        if (!isset($options['redirect_uri'])) {
            $options['redirect_uri'] = $this->clientMetadata->redirectUri();
        }

        $options['client_id'] = $this->clientMetadata->id();

        return $options;
    }

    /**
     * @param array $parameters
     * @param array $checks
     * @return TokenSetInterface
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
     * @param mixed $grant
     * @param array $options
     * @return TokenSetInterface
     */
    private function getTokenSet($grant, array $options = []): TokenSetInterface
    {
        /** @var GrantFactory $grantFactory */
        $grantFactory = $this->container->get(GrantFactory::class);

        $grant = $grantFactory->getGrant($grant);

        $params = array_merge([
            'client_id' => $this->clientMetadata->id(),
            'client_secret' => $this->clientMetadata->secret(),
            'redirect_uri' => $this->clientMetadata->redirectUri(),
        ], $options);

        $params = $grant->prepareRequestParameters($params);

        $request = (new TokenRequestFactory($this->providerMetadata->tokenEndpoint()))
            ->createRequest($params);

        $appender = $this->getTokenRequestAppender();
        $appendedRequest = $appender->withClientAuthentication(
            $request,
            $this->clientMetadata->id(),
            $this->clientMetadata->secret()
        );

        /** @var HttpClientInterface $httpClient */
        $httpClient = $this->container->get(HttpClientInterface::class);

        try {
            $response = $httpClient->send($appendedRequest);
        } catch (BadResponseException $e) {
            throw new OpenIDProviderException('OpenID Connect provider error');
        }

        $content = (string)$response->getBody();

        if (strpos($response->getHeaderLine('urlencoded'), 'urlencoded') !== false) {
            parse_str($content, $parsed);
        } else {
            $parsed = \GuzzleHttp\json_decode($content, true);
        }

        if (is_array($parsed) && !empty($parsed['error'])) {
            $error = $parsed['error'];

            throw new OpenIDProviderException($error);
        }

        if (!is_array($parsed)) {
            throw new OpenIDProviderException(
                'Invalid response received from OpenID Provider. Expected JSON.'
            );
        }

        $tokenSet = new TokenSet($parsed, $this->providerMetadata, $this->clientMetadata);

        if (!$tokenSet->hasIdToken()) {
            throw new OpenIDProviderException("'id_token' missing from the token endpoint response");
        }

        return $tokenSet;
    }
}
