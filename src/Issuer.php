<?php

namespace OpenIDConnect;

use GuzzleHttp\Client;
use Jose\Component\Core\JWK;
use Jose\Component\Core\JWKSet;
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
     * @return array [ProviderMetadata, JWKSet]
     */
    public static function discover(string $uri, bool $raw = false, array $httpOption = []): array
    {
        $httpClient = new Client($httpOption);

        $discoveryUri = $uri . self::OPENID_CONNECT_DISCOVERY;

        $response = $httpClient->request('GET', $discoveryUri);

        $providerMetadata = ProviderMetadata::create(self::processResponse($response));

        $jwkResponse = self::processResponse($httpClient->request('GET', $providerMetadata->jwksUri()));

        $jwkSet = JWKSet::createFromKeyData($jwkResponse);

        if (!$raw) {
            return [$providerMetadata, $jwkSet];
        }

        $rawProviderMetadata = $providerMetadata->toArray();

        $rawJwkSet = array_values(array_map(static function (JWK $v) {
            return $v->jsonSerialize();
        }, $jwkSet->all()));

        return [$rawProviderMetadata, ['keys' => $rawJwkSet]];
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
