<?php

namespace OpenIDConnect\Metadata;

use ArrayAccess;
use OpenIDConnect\Jwt\Factory;
use OpenIDConnect\Traits\MetadataAwareTraits;
use OutOfBoundsException;
use RuntimeException;

/**
 * OAuth 2.0 / OpenID Connect provider metadata
 *
 * @see https://tools.ietf.org/html/rfc8414#section-2
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
     * @var array|null
     */
    private $jwks;

    /**
     * @param array $metadata
     * @param array|null $jwks
     */
    public function __construct(array $metadata = [], array $jwks = null)
    {
        $this->metadata = collect($metadata);
        $this->jwks = $jwks;

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
     * @return Factory
     */
    public function createJwtFactory(): Factory
    {
        return new Factory($this);
    }

    /**
     * @return array
     */
    public function idTokenAlgValuesSupported(): array
    {
        $signing = $this->idTokenSigningAlgValuesSupported();

        $encryption = $this->idTokenEncryptionAlgValuesSupported() ?? [];

        return array_unique(array_merge($signing, $encryption));
    }

    /**
     * @return array|null
     */
    public function idTokenEncryptionAlgValuesSupported(): ?array
    {
        return $this->metadata['id_token_encryption_alg_values_supported'] ?? null;
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
     * @return JwkMetadata
     */
    public function jwkMetadata(): JwkMetadata
    {
        if (null === $this->jwks) {
            throw new RuntimeException('JWK metadata is empty');
        }

        return new JwkMetadata($this->jwks);
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
