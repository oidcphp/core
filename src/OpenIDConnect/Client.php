<?php

namespace OpenIDConnect;

use GuzzleHttp\ClientInterface as HttpClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use InvalidArgumentException;
use OpenIDConnect\Exceptions\EntryNotFoundException;
use OpenIDConnect\Exceptions\OpenIDProviderException;
use OpenIDConnect\Exceptions\RelyingPartyException;
use OpenIDConnect\Http\QueryProcessorTrait;
use OpenIDConnect\Http\TokenRequestFactory;
use OpenIDConnect\Metadata\ClientRegistration;
use OpenIDConnect\Metadata\MetadataAwareTraits;
use OpenIDConnect\Metadata\ProviderMetadata;
use OpenIDConnect\OAuth2\Grant\GrantFactory;
use OpenIDConnect\Token\TokenSet;
use OpenIDConnect\Token\TokenSetInterface;
use OpenIDConnect\Traits\ClientAuthenticationAwareTrait;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use function GuzzleHttp\json_decode;

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
     * @param ClientRegistration $clientRegistration
     * @param ContainerInterface $container The container implements PSR-11
     */
    public function __construct(
        ProviderMetadata $providerMetadata,
        ClientRegistration $clientRegistration,
        ContainerInterface $container
    ) {
        $this->setProviderMetadata($providerMetadata);
        $this->setClientRegistration($clientRegistration);
        $this->setContainer($container);
    }

    /**
     * Create PSR-7 response with form post
     *
     * @param array $options
     * @return ResponseInterface
     */
    public function createAuthorizeFormPostResponse(array $options = []): ResponseInterface
    {
        /** @var ResponseFactoryInterface $responseFactory */
        $responseFactory = $this->container->get(ResponseFactoryInterface::class);

        /** @var StreamFactoryInterface $streamFactory */
        $streamFactory = $this->container->get(StreamFactoryInterface::class);

        return $responseFactory->createResponse()
            ->withBody($streamFactory->createStream($this->getAuthorizationPost($options)));
    }

    /**
     * Create PSR-7 response with redirect info
     *
     * @param array $options
     * @return ResponseInterface
     */
    public function createAuthorizeRedirectResponse(array $options = []): ResponseInterface
    {
        /** @var ResponseFactoryInterface $responseFactory */
        $responseFactory = $this->container->get(ResponseFactoryInterface::class);

        return $responseFactory->createResponse(302)
            ->withHeader('Location', (string)$this->getAuthorizationUri($options));
    }

    /**
     * @param array $options
     * @return UriInterface
     */
    public function getAuthorizationUri(array $options = []): UriInterface
    {
        /** @var UriFactoryInterface $uriFactory */
        $uriFactory = $this->container->get(UriFactoryInterface::class);

        $params = $this->getAuthorizationParameters($options);

        return $uriFactory->createUri($this->providerMetadata->authorizationEndpoint())
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
            $options['redirect_uri'] = $this->clientRegistration->redirectUri();
        }

        $options['client_id'] = $this->clientRegistration->id();

        return $options;
    }

    /**
     * @param array $parameters
     * @param array $checks
     * @return TokenSetInterface
     */
    public function handleOpenIDConnectCallback(array $parameters, array $checks = []): TokenSetInterface
    {
        if (!isset($parameters['code'])) {
            throw new InvalidArgumentException("'code' missing from the response");
        }

        if (!isset($checks['redirect_uri'])) {
            throw new InvalidArgumentException("'redirect_uri' argument is missing");
        }

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
            'redirect_uri' => $checks['redirect_uri'],
        ]);
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
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
            'client_id' => $this->clientRegistration->id(),
            'client_secret' => $this->clientRegistration->secret(),
        ], $options);

        $params = $grant->prepareRequestParameters($params);

        $request = (new TokenRequestFactory($this->providerMetadata->tokenEndpoint()))
            ->createRequest($params);

        $appender = $this->getTokenRequestAppender();
        $appendedRequest = $appender->withClientAuthentication(
            $request,
            $this->clientRegistration->id(),
            $this->clientRegistration->secret()
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
            $parsed = json_decode($content, true);
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

        $tokenSet = new TokenSet($parsed, $this->providerMetadata, $this->clientRegistration);

        if (!$tokenSet->hasIdToken()) {
            throw new OpenIDProviderException("'id_token' missing from the token endpoint response");
        }

        return $tokenSet;
    }

    /**
     * @param string $accessToken
     * @return array
     */
    public function getUserInfo(string $accessToken): array
    {
        /** @var RequestFactoryInterface $requestFactory */
        $requestFactory = $this->container->get(RequestFactoryInterface::class);

        /** @var UriFactoryInterface $uriFactory */
        $uriFactory = $this->container->get(UriFactoryInterface::class);

        if (null === $this->providerMetadata->userInfoEndpoint()) {
            throw new OpenIDProviderException('Provider does not support user info endpoint');
        }

        $uri = $uriFactory->createUri($this->providerMetadata->userInfoEndpoint());

        $request = $requestFactory->createRequest('GET', $uri)
            ->withHeader('Authorization', 'Bearer ' . $accessToken);

        /** @var HttpClientInterface $httpClient */
        $httpClient = $this->container->get(HttpClientInterface::class);

        $response = $httpClient->send($request);

        return json_decode((string)$response->getBody(), true);
    }

    /**
     * @param ContainerInterface $container
     * @return Client
     */
    public function setContainer(ContainerInterface $container): Client
    {
        $entries = [
            GrantFactory::class,
            HttpClientInterface::class,
            StreamFactoryInterface::class,
            ResponseFactoryInterface::class,
            RequestFactoryInterface::class,
            UriFactoryInterface::class,
        ];

        foreach ($entries as $entry) {
            if (!$container->has($entry)) {
                throw new EntryNotFoundException("The entry '$entry' is not found");
            }
        }

        $this->container = $container;

        return $this;
    }
}
