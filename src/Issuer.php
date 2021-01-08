<?php

namespace OpenIDConnect;

use MilesChou\Psr\Http\Client\HttpClientAwareTrait;
use MilesChou\Psr\Http\Client\HttpClientInterface;
use OpenIDConnect\Contracts\ClientMetadataInterface;
use OpenIDConnect\Contracts\ProviderMetadataInterface;
use OpenIDConnect\Exceptions\OpenIDProviderException;
use OpenIDConnect\Exceptions\RelyingPartyException;
use OpenIDConnect\Jwt\JwkSet;
use OpenIDConnect\Metadata\ProviderMetadata;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Discover provider config
 */
class Issuer
{
    use HttpClientAwareTrait;

    /**
     * @param HttpClientInterface $httpClient
     */
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->setHttpClient($httpClient);
    }

    /**
     * Discover the OpenID Connect provider
     *
     * @param string $uri
     * @return ProviderMetadata
     * @throws ClientExceptionInterface
     * @see https://tools.ietf.org/html/rfc8414#section-3
     * @see https://openid.net/specs/openid-connect-discovery-1_0.html#ProviderConfig
     */
    public function discover(string $uri): ProviderMetadata
    {
        $discover = $this->sendRequest($uri);

        return new ProviderMetadata($discover, $this->jwkSet($discover['jwks_uri']));
    }

    /**
     * Download JWKs
     *
     * @param string $jwksUri
     * @return JwkSet
     * @throws ClientExceptionInterface
     */
    public function jwkSet(string $jwksUri): JwkSet
    {
        return new JwkSet($this->sendRequest($jwksUri));
    }

    /**
     * @param ProviderMetadataInterface $providerMetadata
     * @param ClientMetadataInterface $clientMetadata
     * @return ClientMetadataInterface
     * @throws ClientExceptionInterface
     */
    public function register(
        ProviderMetadataInterface $providerMetadata,
        ClientMetadataInterface $clientMetadata
    ): ClientMetadataInterface {
        if (!$providerMetadata->has('registration_endpoint')) {
            $msg = 'Cannot use dynamic client registration on issuer: ' . $providerMetadata->get('issuer');

            throw new RelyingPartyException($msg);
        }

        $registrationEndpoint = $providerMetadata->get('registration_endpoint');

        $request = $this->httpClient->createRequest('POST', $registrationEndpoint)
            ->withHeader('content-type', 'application/json')
            ->withBody($this->httpClient->createStream((string)json_encode($clientMetadata)));

        $response = $this->processResponse($this->httpClient->sendRequest($request));

        return $clientMetadata->merge($response);
    }

    /**
     * Send request to discovery endpoint and process response
     *
     * @param string $uri
     * @return array
     * @throws ClientExceptionInterface
     */
    private function sendRequest(string $uri): array
    {
        $response = $this->httpClient->sendRequest(
            $this->httpClient->createRequest('GET', $uri)
        );

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
