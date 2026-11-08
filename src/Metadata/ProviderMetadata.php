<?php

declare(strict_types=1);

namespace OpenIDConnect\Metadata;

use JsonSerializable;
use OpenIDConnect\Jwt\JwkSet;
use OpenIDConnect\Traits\ParameterTrait;

/**
 * OAuth 2.0 Authorization Server Metadata
 *
 * @method string|null issuer()
 * @method string|null authorizationEndpoint()
 * @method string|null jwksUri()
 * @method string|null registrationEndpoint()
 * @method array<string>|null scopesSupported()
 * @method array<string>|null responseTypesSupported()
 * @method array<string>|null responseModesSupported()
 * @method array<string>|null grantTypesSupported()
 * @method string|null tokenEndpoint()
 * @method array<string>|null tokenEndpointAuthMethodsSupported()
 * @method array<string>|null tokenEndpointAuthSigningAlgValuesSupported()
 * @method string|null revocationEndpoint()
 * @method array<string>|null revocationEndpointAuthMethodsSupported()
 * @method array<string>|null revocationEndpointAuthSigningAlgValuesSupported()
 * @method string|null introspectionEndpoint()
 * @method array<string>|null introspectionEndpointAuthMethodsSupported()
 * @method array<string>|null introspectionEndpointAuthSigningAlgValuesSupported()
 * @method array<string>|null codeChallengeMethodsSupported() PKCE [RFC7636] code challenge methods supported
 * @method string|null serviceDocumentation() URL of a page containing human-readable information for developer
 * @method array<string>|null uiLocalesSupported() Languages and scripts supported for the user interface
 * @method string|null opPolicyUri() URL that how the client can use the data provided
 * @method string|null opTosUri() URL that about the authorization server's terms of service
 *
 * @link https://www.rfc-editor.org/rfc/rfc8414.html#section-2
 */
class ProviderMetadata implements JsonSerializable
{
    use ParameterTrait;

    /**
     * @var JwkSet
     */
    private JwkSet $jwkSet;

    /**
     * @param array<string, mixed> $metadata
     * @param array<string, mixed> $jwkSet
     */
    public function __construct(array $metadata, array $jwkSet = [])
    {
        $this->parameters = $metadata;
        $this->jwkSet = new JwkSet($jwkSet);
    }

    /**
     * @param array<string, mixed> $jwk JWK array
     * @return ProviderMetadata
     */
    public function addJwk(array $jwk): ProviderMetadata
    {
        $this->jwkSet->add($jwk);

        return $this;
    }

    public function jwkSet(): JwkSet
    {
        return $this->jwkSet;
    }
}
