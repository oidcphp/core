<?php

namespace OpenIDConnect\Core;

use OpenIDConnect\Core\Exceptions\OpenIDProviderException;
use OpenIDConnect\Core\Exceptions\RelyingPartyException;
use OpenIDConnect\OAuth2\Metadata\ClientInformation;
use OpenIDConnect\OAuth2\Metadata\ClientMetadata;
use OpenIDConnect\OAuth2\Metadata\JwkSet;
use OpenIDConnect\OAuth2\Metadata\ProviderMetadata;
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
     * @var ClientInterface
     */
    private $client;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    /**
     * @param ClientInterface $client
     * @param RequestFactoryInterface $requestFactory
     * @param StreamFactoryInterface $streamFactory
     */
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
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

        $request = $this->requestFactory->createRequest('POST', $registrationEndpoint)
            ->withHeader('content-type', 'application/json')
            ->withBody($this->streamFactory->createStream((string)json_encode($clientMetadata)));

        return new ClientInformation($this->processResponse($this->client->sendRequest($request)));
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
        $response = $this->client->sendRequest($this->requestFactory->createRequest('GET', $uri));

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
