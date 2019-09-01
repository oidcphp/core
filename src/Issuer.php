<?php

namespace OpenIDConnect;

use GuzzleHttp\ClientInterface as HttpClientInterface;
use OpenIDConnect\Exceptions\RelyingPartyException;
use OpenIDConnect\Metadata\ProviderMetadata;
use OpenIDConnect\Traits\HttpClientAwareTrait;
use Psr\Http\Message\ResponseInterface;
use UnexpectedValueException;
use function GuzzleHttp\json_decode;

/**
 * OpenID Provider Issuer Discovery
 *
 * @see https://openid.net/specs/openid-connect-discovery-1_0.html#IssuerDiscovery
 */
class Issuer
{
    use HttpClientAwareTrait;

    /**
     * @see https://openid.net/specs/openid-connect-discovery-1_0.html#ProviderConfig
     */
    public const OPENID_CONNECT_DISCOVERY = '/.well-known/openid-configuration';

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var string|null
     */
    private $jwksUri;

    /**
     * @param string $baseUrl
     * @param string|null $jwksUri
     * @param HttpClientInterface|null $httpClient
     * @return static
     */
    public static function create(string $baseUrl, ?string $jwksUri = null, ?HttpClientInterface $httpClient = null)
    {
        return new static($baseUrl, $jwksUri, $httpClient);
    }

    /**
     * @param string $baseUrl
     * @param string|null $jwksUri
     * @param HttpClientInterface|null $httpClient
     */
    public function __construct(string $baseUrl, ?string $jwksUri = null, ?HttpClientInterface $httpClient = null)
    {
        $this->baseUrl = $baseUrl;
        $this->jwksUri = $jwksUri;

        if (null !== $httpClient) {
            $this->setHttpClient($httpClient);
        }
    }

    /**
     * Discover the OpenID Connect provider
     *
     * @return ProviderMetadata
     */
    public function discover(): ProviderMetadata
    {
        $httpClient = $this->getHttpClient();

        $discoveryUri = $this->normalizeUrl($this->baseUrl) . self::OPENID_CONNECT_DISCOVERY;

        $discoverResponse = $this->processResponse($httpClient->request('GET', $discoveryUri));
        $jwksResponse = $this->processResponse($httpClient->request('GET', $this->resolveJwksUri($discoverResponse)));

        return new ProviderMetadata($discoverResponse, $jwksResponse);
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

    /**
     * @param array $discoverResponse
     * @return string
     */
    private function resolveJwksUri(array $discoverResponse): string
    {
        if (null === $this->jwksUri && empty($discoverResponse['jwks_uri'])) {
            throw new RelyingPartyException("Missing 'jwks_url` metadata");
        }

        if (null === $this->jwksUri) {
            return $discoverResponse['jwks_uri'];
        }

        return $this->jwksUri;
    }
}
