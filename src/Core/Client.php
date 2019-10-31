<?php

namespace OpenIDConnect\Core;

use GuzzleHttp\ClientInterface as HttpClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use InvalidArgumentException;
use OpenIDConnect\Core\Exceptions\EntryNotFoundException;
use OpenIDConnect\Core\Exceptions\OpenIDProviderException;
use OpenIDConnect\Core\Exceptions\RelyingPartyException;
use OpenIDConnect\Core\Metadata\ClientRegistration;
use OpenIDConnect\Core\Metadata\MetadataAwareTraits;
use OpenIDConnect\Core\Metadata\ProviderMetadata;
use OpenIDConnect\Core\OAuth2\Grant\GrantFactory;
use OpenIDConnect\Core\OAuth2\TokenRequestFactory;
use OpenIDConnect\Core\Token\TokenSet;
use OpenIDConnect\Core\Token\TokenSetInterface;
use OpenIDConnect\Core\Traits\ClientAuthenticationAwareTrait;
use OpenIDConnect\Support\Http\Query;
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

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var null|string
     */
    private $nonce;

    /**
     * @var null|string
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
     * @param array $options
     * @return string
     */
    public function createAuthorizeForm(array $options = []): string
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
            ->withBody($streamFactory->createStream($this->createAuthorizeForm($options)));
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
            ->withHeader('Location', (string)$this->createAuthorizeUri($options));
    }

    /**
     * @param array $options
     * @return UriInterface
     */
    public function createAuthorizeUri(array $options = []): UriInterface
    {
        /** @var UriFactoryInterface $uriFactory */
        $uriFactory = $this->container->get(UriFactoryInterface::class);

        $parameters = $this->getAuthorizationParameters($options);

        return $uriFactory->createUri($this->providerMetadata->authorizationEndpoint())
            ->withQuery(Query::build($parameters));
    }

    /**
     * @return null|string
     */
    public function getNonce(): ?string
    {
        return $this->nonce;
    }

    /**
     * @return null|string
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string $accessToken
     * @return array
     */
    public function getUserInfo(string $accessToken): array
    {
        /** @var RequestFactoryInterface $requestFactory */
        $requestFactory = $this->container->get(RequestFactoryInterface::class);

        $userInfoEndpoint = $this->providerMetadata->userInfoEndpoint();

        if (null === $userInfoEndpoint) {
            throw new OpenIDProviderException('Provider does not support user info endpoint');
        }

        $request = $requestFactory->createRequest('GET', $userInfoEndpoint)
            ->withHeader('Authorization', 'Bearer ' . $accessToken);

        /** @var HttpClientInterface $httpClient */
        $httpClient = $this->container->get(HttpClientInterface::class);

        $response = $httpClient->send($request);

        return json_decode((string)$response->getBody(), true);
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

        return $this->getTokenSet('authorization_code', $parameters, $checks);
    }

    /**
     * Initial the authorization parameters
     *
     * @param array $options
     */
    public function initAuthorizationParameters(array $options = []): void
    {
        if (!empty($options['nonce'])) {
            $this->nonce = $options['nonce'];
        }

        if (null === $this->nonce) {
            $this->nonce = $this->generateRandomString();
        }

        if (!empty($options['state'])) {
            $this->state = $options['state'];
        }

        if (null === $this->state) {
            $this->state = $this->generateRandomString();
        }
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

    /**
     * Generate the random string
     *
     * @param int $length
     * @return string
     */
    protected function generateRandomString(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * @param array $options
     * @return array
     */
    protected function getAuthorizationParameters(array $options): array
    {
        $this->initAuthorizationParameters($options);

        $options['nonce'] = $this->nonce;
        $options['state'] = $this->state;

        if (empty($options['scope'])) {
            $options['scope'] = ['openid'];
        }

        $options += [
            'response_type' => 'code',
        ];

        if (is_array($options['scope'])) {
            $options['scope'] = implode(' ', $options['scope']);
        }

        // Business code layer might set a different redirect_uri parameter
        // depending on the context, leave it as-is
        if (!isset($options['redirect_uri'])) {
            $options['redirect_uri'] = $this->clientRegistration->redirectUri();
        }

        $options['client_id'] = $this->clientRegistration->id();

        return $options;
    }

    /**
     * @param mixed $grant
     * @param array $parameters
     * @param array $checks
     * @return TokenSetInterface
     */
    private function getTokenSet($grant, array $parameters = [], array $checks = []): TokenSetInterface
    {
        /** @var GrantFactory $grantFactory */
        $grantFactory = $this->container->get(GrantFactory::class);

        $grant = $grantFactory->getGrant($grant);

        $parameters = $grant->prepareRequestParameters(array_merge($parameters, $checks));

        $request = (new TokenRequestFactory($parameters))
            ->createRequest('POST', $this->providerMetadata->tokenEndpoint());

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
            $msg = 'OpenID Connect provider error: ' . $e->getMessage();
            throw new OpenIDProviderException($msg, 0, $e);
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

        $tokenSet = new TokenSet(array_merge($checks, $parsed), $this->providerMetadata, $this->clientRegistration);

        if (!$tokenSet->hasIdToken()) {
            throw new OpenIDProviderException("'id_token' missing from the token endpoint response");
        }

        return $tokenSet;
    }
}
