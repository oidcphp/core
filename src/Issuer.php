<?php

namespace OpenIDConnect;

use GuzzleHttp\Client;
use OpenIDConnect\Metadata\JWKMetadata;
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
     * @param string $uri
     * @param bool $raw
     * @param array $httpOption
     * @return array [ProviderMetadata, JWKMetadata]
     */
    public static function discover(string $uri, bool $raw = false, array $httpOption = []): array
    {
        $httpClient = new Client($httpOption);

        $discoveryUri = $uri . self::OPENID_CONNECT_DISCOVERY;

        $providerResponse = $httpClient->request('GET', $discoveryUri);
        $providerMetadata = new ProviderMetadata(self::processResponse($providerResponse));

        $jwkResponse = $httpClient->request('GET', $providerMetadata->jwksUri());
        $jwkMetadata = new JWKMetadata(self::processResponse($jwkResponse));

        if (!$raw) {
            return [$providerMetadata, $jwkMetadata];
        }

        return [$providerMetadata->toArray(), $jwkMetadata->toArray()];
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
}
