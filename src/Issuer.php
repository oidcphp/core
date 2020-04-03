<?php

namespace OpenIDConnect;

use MilesChou\Psr\Http\Message\HttpFactoryInterface;
use OpenIDConnect\Core\Exceptions\OpenIDProviderException;
use OpenIDConnect\Core\Exceptions\RelyingPartyException;
use OpenIDConnect\Jwt\JwkSet;
use OpenIDConnect\Metadata\ClientInformation;
use OpenIDConnect\Metadata\ClientMetadata;
use OpenIDConnect\Metadata\ProviderMetadata;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Discover provider config
 */
class Issuer
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var HttpFactoryInterface
     */
    private $httpFactory;

    /**
     * @param ClientInterface $client
     * @param HttpFactoryInterface $httpFactory
     */
    public function __construct(ClientInterface $client, HttpFactoryInterface $httpFactory)
    {
        $this->client = $client;
        $this->httpFactory = $httpFactory;
    }

    /**
     * Discover the OpenID Connect provider
     *
     * @param string $discoverUri
     *
     * @return ProviderMetadata
     * @throws ClientExceptionInterface
     * @see https://tools.ietf.org/html/rfc8414#section-3
     * @see https://openid.net/specs/openid-connect-discovery-1_0.html#ProviderConfig
     */
    public function discover(string $discoverUri): ProviderMetadata
    {
        $discoverResponse = $this->sendRequestDiscovery($discoverUri);
        $jwksResponse = $this->sendRequestDiscovery($discoverResponse['jwks_uri']);

        return new ProviderMetadata($discoverResponse, new JwkSet($jwksResponse));
    }

    /**
     * @param ProviderMetadata $providerMetadata
     * @param ClientMetadata $clientMetadata
     *
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

        $request = $this->httpFactory->createRequest('POST', $registrationEndpoint)
            ->withHeader('content-type', 'application/json')
            ->withBody($this->httpFactory->createStream((string)json_encode($clientMetadata)));

        return new ClientInformation($this->processResponse($this->client->sendRequest($request)));
    }

    /**
     * Send request to discovery endpoint and process response
     *
     * @param string $uri
     *
     * @return array
     * @throws ClientExceptionInterface
     */
    private function sendRequestDiscovery(string $uri): array
    {
        $response = $this->client->sendRequest($this->httpFactory->createRequest('GET', $uri));

        return $this->processResponse($response);
    }

    /**
     * @param ResponseInterface $response
     *
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
