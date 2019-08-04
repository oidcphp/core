<?php

namespace OpenIDConnect;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface as HttpClientInterface;
use Jose\Component\Core\JWKSet;
use Psr\Http\Message\ResponseInterface;
use UnexpectedValueException;
use function GuzzleHttp\json_decode;

/**
 * OpenID Connect provider discoverer
 */
class Discoverer
{
    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @see https://openid.net/specs/openid-connect-discovery-1_0.html
     */
    public const OPENID_CONNECT_DISCOVERY = '/.well-known/openid-configuration';

    /**
     * @param array $httpOption
     */
    public function __construct(array $httpOption = [])
    {
        $this->httpClient = new Client($httpOption);
    }

    /**
     * Discover the OpenID Connect provider
     *
     * @param string $uri
     * @return ProviderMetadata
     */
    public function discover(string $uri): ProviderMetadata
    {
        $discoveryUri = $uri . self::OPENID_CONNECT_DISCOVERY;

        $response = $this->httpClient->request('GET', $discoveryUri);

        return ProviderMetadata::create($this->processResponse($response));
    }

    /**
     * @param string $uri
     * @return JWKSet
     */
    public function keystore(string $uri): JWKSet
    {
        $jwksArray = $this->processResponse($this->httpClient->request('GET', $uri));

        return JWKSet::createFromKeyData($jwksArray);
    }

    /**
     * @param HttpClientInterface $httpClient
     */
    public function setHttpClient(HttpClientInterface $httpClient): void
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param ResponseInterface $response
     * @return array
     */
    private function processResponse(ResponseInterface $response): array
    {
        if (200 !== $response->getStatusCode()) {
            throw new UnexpectedValueException('Server Error');
        }

        return json_decode((string)$response->getBody(), true);
    }
}
