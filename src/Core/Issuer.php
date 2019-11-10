<?php

namespace OpenIDConnect\Core;

use OpenIDConnect\Core\Exceptions\OpenIDProviderException;
use OpenIDConnect\Core\Exceptions\RelyingPartyException;
use OpenIDConnect\OAuth2\Metadata\ClientInformation;
use OpenIDConnect\OAuth2\Metadata\ClientMetadata;
use OpenIDConnect\OAuth2\Metadata\JwkSet;
use OpenIDConnect\OAuth2\Metadata\ProviderMetadata;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * OpenID Provider Issuer Discovery
 *
 * @see https://openid.net/specs/openid-connect-discovery-1_0.html#IssuerDiscovery
 */
class Issuer
{
    /**
     * @see https://openid.net/specs/openid-connect-discovery-1_0.html#ProviderConfig
     */
    public const OPENID_CONNECT_DISCOVERY = '/.well-known/openid-configuration';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     * @return static
     */
    public static function create(ContainerInterface $container): Issuer
    {
        return new static($container);
    }

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Discover the OpenID Connect provider
     *
     * @param string $baseUrl
     * @return ProviderMetadata
     * @throws ClientExceptionInterface
     */
    public function discover(string $baseUrl): ProviderMetadata
    {
        $discoveryUri = $this->normalizeUrl($baseUrl) . self::OPENID_CONNECT_DISCOVERY;

        $discoverResponse = $this->sendRequestDiscovery($discoveryUri);
        $jwksResponse = $this->sendRequestDiscovery($discoverResponse['jwks_uri']);

        return new ProviderMetadata($discoverResponse, new JwkSet($jwksResponse));
    }

    /**
     * @param ProviderMetadata $providerMetadata
     * @param ClientMetadata $clientMetadata
     * @return ClientInformation
     * @throws ClientExceptionInterface
     */
    public function register(ProviderMetadata $providerMetadata, ClientMetadata $clientMetadata): ClientInformation
    {
        $registrationEndpoint = $providerMetadata->registrationEndpoint();

        if (empty($registrationEndpoint)) {
            $msg = 'Cannot use dynamic client registration on issuer: ' . $providerMetadata->issuer();

            throw new RelyingPartyException($msg);
        }

        /** @var ClientInterface $httpClient */
        $httpClient = $this->container->get(ClientInterface::class);

        /** @var RequestFactoryInterface $requestFactory */
        $requestFactory = $this->container->get(RequestFactoryInterface::class);

        /** @var StreamFactoryInterface $streamFactory */
        $streamFactory = $this->container->get(StreamFactoryInterface::class);

        $request = $requestFactory->createRequest('POST', $registrationEndpoint)
            ->withHeader('content-type', 'application/json')
            ->withBody($streamFactory->createStream((string)json_encode($clientMetadata)));

        return new ClientInformation($this->processResponse($httpClient->sendRequest($request)));
    }

    /**
     * @param string $uri
     * @return string
     */
    private function normalizeUrl(string $uri): string
    {
        $uri = str_replace(self::OPENID_CONNECT_DISCOVERY, '', $uri);
        $uri = rtrim($uri, '/');

        return $uri;
    }

    /**
     * Send request to discovery endpoint and process response
     *
     * @param string $uri
     * @return array
     * @throws ClientExceptionInterface
     */
    private function sendRequestDiscovery(string $uri): array
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->container->get(ClientInterface::class);

        /** @var RequestFactoryInterface $requestFactory */
        $requestFactory = $this->container->get(RequestFactoryInterface::class);

        $response = $httpClient->sendRequest($requestFactory->createRequest('GET', $uri));

        return $this->processResponse($response);
    }

    /**
     * @param ResponseInterface $response
     * @return array
     */
    private function processResponse(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();
        if (200 > $statusCode || $statusCode >= 300) {
            throw new OpenIDProviderException('Response status code is not 2xx, Given is ' . $statusCode);
        }

        return json_decode((string)$response->getBody(), true);
    }
}
