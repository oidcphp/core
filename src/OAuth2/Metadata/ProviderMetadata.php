<?php

declare(strict_types=1);

namespace OpenIDConnect\OAuth2\Metadata;

use JsonSerializable;
use OpenIDConnect\OAuth2\Traits\ParameterTrait;

/**
 * OAuth 2.0 Authorization Server Metadata
 *
 * @method string|null issuer()
 * @method string|null authorizationEndpoint()
 * @method string|null jwksUri()
 * @method string|null registrationEndpoint()
 * @method array|null scopesSupported()
 * @method array|null responseTypesSupported()
 * @method array|null responseModesSupported()
 * @method array|null grantTypesSupported()
 * @method string|null tokenEndpoint()
 * @method array|null tokenEndpointAuthMethodsSupported()
 * @method array|null tokenEndpointAuthSigningAlgValuesSupported()
 * @method string|null revocationEndpoint()
 * @method array|null revocationEndpointAuthMethodsSupported()
 * @method array|null revocationEndpointAuthSigningAlgValuesSupported()
 * @method string|null introspectionEndpoint()
 * @method array|null introspectionEndpointAuthMethodsSupported()
 * @method array|null introspectionEndpointAuthSigningAlgValuesSupported()
 * @method array|null codeChallengeMethodsSupported() PKCE [RFC7636] code challenge methods supported
 * @method string|null serviceDocumentation() URL of a page containing human-readable information for developer
 * @method array|null uiLocalesSupported() Languages and scripts supported for the user interface
 * @method string|null opPolicyUri() URL that how the client can use the data provided
 * @method string|null opTosUri() URL that about the authorization server's terms of service
 *
 * @see https://tools.ietf.org/html/rfc8414#section-2
 */
class ProviderMetadata implements JsonSerializable
{
    use ParameterTrait;

    /**
     * @var JwkSet
     */
    private $jwkSet;

    /**
     * @param array<mixed> $metadata
     * @param JwkSet|null $jwkSet
     */
    public function __construct(array $metadata, ?JwkSet $jwkSet = null)
    {
        $this->parameters = $metadata;

        if (null === $jwkSet) {
            $jwkSet = new JwkSet();
        }

        $this->jwkSet = $jwkSet;
    }

    /**
     * @param array<mixed> $jwk JWK array
     * @return ProviderMetadata
     */
    public function addJwk($jwk): ProviderMetadata
    {
        $this->jwkSet->add($jwk);

        return $this;
    }

    /**
     * @return JwkSet
     */
    public function jwkSet(): JwkSet
    {
        return $this->jwkSet;
    }
}
