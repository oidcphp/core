<?php

namespace OpenIDConnect\Metadata;

use ArrayAccess;
use Jose\Component\Checker\AlgorithmChecker;
use Jose\Component\Checker\HeaderCheckerManager;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Encryption\JWETokenSupport;
use Jose\Component\Signature\JWSTokenSupport;
use OpenIDConnect\Jwt\AlgorithmFactory;
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
     * @var AlgorithmFactory
     */
    private $algorithmFactory;

    /**
     * @param array $metadata
     * @param AlgorithmFactory|null $algorithmFactory
     */
    public function __construct(array $metadata = [], AlgorithmFactory $algorithmFactory = null)
    {
        $this->metadata = collect($metadata);

        if (!$this->metadata->has(self::REQUIRED_METADATA)) {
            throw new OutOfBoundsException('Required config is missing. Config: ' . $this->metadata->toJson());
        }

        if (null === $algorithmFactory) {
            $this->algorithmFactory = new AlgorithmFactory();
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
     * @return AlgorithmManager
     */
    public function createAlgorithmManager(): AlgorithmManager
    {
        return $this->algorithmFactory->createAlgorithmManager($this->idTokenAlgValuesSupported());
    }

    /**
     * @return HeaderCheckerManager
     */
    public function createHeaderCheckerManager(): HeaderCheckerManager
    {
        $tokenTypesSupport = [new JWSTokenSupport()];

        if (null !== $this->idTokenEncryptionAlgValuesSupported()) {
            $tokenTypesSupport[] = new JWETokenSupport();
        }

        return HeaderCheckerManager::create([
            new AlgorithmChecker($this->idTokenAlgValuesSupported()),
        ], $tokenTypesSupport);
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
