<?php

namespace OpenIDConnect\Metadata;

use ArrayAccess;
use Jose\Component\Core\JWK;
use Jose\Component\Core\JWKSet;
use JsonSerializable;
use OpenIDConnect\Jwt\JwtFactory;

/**
 * OAuth 2.0 / OpenID Connect provider metadata
 *
 * @see https://tools.ietf.org/html/rfc8414#section-2
 * @see https://openid.net/specs/openid-connect-discovery-1_0.html#ProviderMetadata
 */
class ProviderMetadata implements ArrayAccess, JsonSerializable
{
    use MetadataTraits;

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
     * @var array
     */
    private $additionAlgorithms = [];

    /**
     * @var JWKSet
     */
    private $jwkSet;

    /**
     * @param array $metadata
     * @param array|null $jwks The metadata from `jwks_uri`
     */
    public function __construct(array $metadata, array $jwks = null)
    {
        $this->metadata = $metadata;

        if (null === $jwks) {
            $this->jwkSet = new JWKSet([]);
        } else {
            $this->jwkSet = JWKSet::createFromKeyData($jwks);
        }

        $this->assertKeys(self::REQUIRED_METADATA);
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
     * @param ClientMetadata $clientMetadata
     * @return JwtFactory
     */
    public function createJwtFactory(ClientMetadata $clientMetadata): JwtFactory
    {
        $factory = new JwtFactory($this, $clientMetadata);
        $factory->withAlgorithm($this->additionAlgorithms);

        return $factory;
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
     * {@inheritDoc}
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @return string
     */
    public function jwksUri(): string
    {
        return $this->metadata['jwks_uri'];
    }

    /**
     * @return JWKSet
     */
    public function jwkSet(): JWKSet
    {
        return $this->jwkSet;
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
     * @return array
     */
    public function toArray(): array
    {
        $jwks = $this->jwkSet->jsonSerialize();
        $jwks['keys'] = array_map(function (JWK $jwk) {
            return $jwk->jsonSerialize();
        }, $jwks['keys']);

        return [
            'discovery' => $this->metadata,
            'jwks' => $jwks,
        ];
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

    /**
     * @param array<int, JWK> $jwkInstances JWK instance array
     * @return static
     */
    public function withJwkInstances(...$jwkInstances)
    {
        /** @var JWK $jwk */
        foreach ($jwkInstances as $jwk) {
            $this->jwkSet = $this->jwkSet->with($jwk);
            $this->additionAlgorithms[] = $jwk->get('alg');
        }

        return $this;
    }
}
