<?php

namespace OpenIDConnect;

use GuzzleHttp\Client;
use OpenIDConnect\Exceptions\RelyingPartyException;
use OpenIDConnect\Metadata\ProviderMetadata;
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
    /**
     * @see https://openid.net/specs/openid-connect-discovery-1_0.html#ProviderConfig
     */
    public const OPENID_CONNECT_DISCOVERY = '/.well-known/openid-configuration';

    /**
     * Discover the OpenID Connect provider
     *
     * @param string $baseUrl
     * @param array $httpOption
     * @param string|null $jwksUri
     * @return ProviderMetadata
     */
    public static function discover(string $baseUrl, array $httpOption = [], string $jwksUri = null): ProviderMetadata
    {
        $httpClient = new Client($httpOption);

        $discoveryUri = self::normalizeUrl($baseUrl) . self::OPENID_CONNECT_DISCOVERY;

        $providerResponse = self::processResponse($httpClient->request('GET', $discoveryUri));

        $jwksUri = self::resolveJwksUri($jwksUri, $providerResponse);

        $jwksResponse = self::processResponse($httpClient->request('GET', $jwksUri));

        return new ProviderMetadata($providerResponse, $jwksResponse);
    }

    /**
     * @param string $uri
     * @return string
     */
    private static function normalizeUrl(string $uri): string
    {
        $uri = str_replace(self::OPENID_CONNECT_DISCOVERY, '', $uri);
        $uri = rtrim($uri, '/');

        return $uri;
    }

    /**
     * @param ResponseInterface $response
     * @return array
     */
    private static function processResponse(ResponseInterface $response): array
    {
        if (200 !== $response->getStatusCode()) {
            throw new UnexpectedValueException('Server Error');
        }

        return json_decode((string)$response->getBody(), true);
    }

    /**
     * @param string|null $jwksUri
     * @param array $providerResponse
     * @return string
     */
    private static function resolveJwksUri($jwksUri, array $providerResponse): string
    {
        if (null !== $jwksUri) {
            $jwksUri = $providerResponse['jwks_uri'];
        }

        if (null === $jwksUri) {
            throw new RelyingPartyException("Missing 'jwks_url` metadata");
        }

        return $jwksUri;
    }
}
