<?php

namespace OpenIDConnect\Metadata;

use ArrayAccess;
use OpenIDConnect\Traits\MetadataAwareTraits;
use OutOfBoundsException;

/**
 * @see https://openid.net/specs/openid-connect-discovery-1_0.html#ProviderMetadata
 */
class ProviderMetadata implements ArrayAccess
{
    use MetadataAwareTraits;

    public const REQUIRED_METADATA = [
        'authorization_endpoint',
        'id_token_signing_alg_values_supported',
        'issuer',
        'jwks_uri',
        'response_types_supported',
        'subject_types_supported',
        'token_endpoint',
    ];

    /**
     * @param array $metadata
     */
    public function __construct(array $metadata = [])
    {
        $this->metadata = collect($metadata);

        if (!$this->metadata->has(self::REQUIRED_METADATA)) {
            throw new OutOfBoundsException('Required config is missing. Config: ' . $this->metadata->toJson());
        }
    }

    /**
     * @return string
     */
    public function authorizationEndpoint(): string
    {
        return $this->metadata['authorization_endpoint'];
    }

    /**
     * @return array|null
     */
    public function claimsSupported(): ?array
    {
        return $this->metadata['claims_supported'] ?? null;
    }

    /**
     * PKCE support
     *
     * @see https://oauth.net/2/pkce/
     * @return array|null
     */
    public function codeChallengeMethodsSupported(): ?array
    {
        return $this->metadata['code_challenge_methods_supported'] ?? null;
    }

    /**
     * @return array
     */
    public function idTokenSigningAlgValuesSupported(): array
    {
        return $this->metadata['id_token_signing_alg_values_supported'];
    }

    /**
     * @return string
     */
    public function issuer(): string
    {
        return $this->metadata['issuer'];
    }

    /**
     * @return string
     */
    public function jwksUri(): string
    {
        return $this->metadata['jwks_uri'];
    }

    /**
     * @return array
     */
    public function responseTypesSupported(): array
    {
        return $this->metadata['response_types_supported'];
    }

    /**
     * @return array|null
     */
    public function scopesSupported(): ?array
    {
        return $this->metadata['scopes_supported'] ?? null;
    }

    /**
     * @return array
     */
    public function subjectTypesSupported(): array
    {
        return $this->metadata['subject_types_supported'];
    }

    /**
     * @return string
     */
    public function tokenEndpoint(): string
    {
        return $this->metadata['token_endpoint'];
    }

    /**
     * @return array|null
     */
    public function tokenEndpointAuthMethodsSupported(): ?array
    {
        return $this->metadata['token_endpoint_auth_methods_supported'] ?? null;
    }

    /**
     * @return string|null
     */
    public function userInfoEndpoint(): ?string
    {
        return $this->metadata['userinfo_endpoint'] ?? null;
    }
}
