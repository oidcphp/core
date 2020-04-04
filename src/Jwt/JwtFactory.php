<?php

namespace OpenIDConnect\Jwt;

use Jose\Component\Checker\AlgorithmChecker;
use Jose\Component\Checker\AudienceChecker;
use Jose\Component\Checker\ClaimCheckerManager;
use Jose\Component\Checker\ExpirationTimeChecker;
use Jose\Component\Checker\HeaderCheckerManager;
use Jose\Component\Checker\IssuedAtChecker;
use Jose\Component\Checker\NotBeforeChecker;
use Jose\Component\Checker\Tests\Stub\IssuerChecker;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Encryption\JWETokenSupport;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\JWSLoader;
use Jose\Component\Signature\JWSTokenSupport;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use OpenIDConnect\Contracts\ClientMetadataInterface;
use OpenIDConnect\Contracts\ProviderMetadataInterface;
use OpenIDConnect\Jwt\Checker\NonceChecker;
use OpenIDConnect\Metadata\ClientMetadata;
use OpenIDConnect\Metadata\ClientMetadataAwareTrait;
use OpenIDConnect\Metadata\ProviderMetadata;
use OpenIDConnect\Metadata\ProviderMetadataAwareTrait;

class JwtFactory
{
    use AlgorithmFactoryTrait;
    use ClientMetadataAwareTrait;
    use ProviderMetadataAwareTrait;

    /**
     * Addition algorithms
     *
     * @var array
     */
    private $algorithms = [];

    public function __construct(ProviderMetadataInterface $providerMetadata, ClientMetadataInterface $clientMetadata)
    {
        $this->setProviderMetadata($providerMetadata);
        $this->setClientMetadata($clientMetadata);
    }

    /**
     * @return AlgorithmManager
     */
    public function createAlgorithmManager(): AlgorithmManager
    {
        return AlgorithmManager::create(
            $this->createAlgorithms($this->resolveAlgorithms())
        );
    }

    /**
     * @param array $check
     * @return ClaimCheckerManager
     * @link https://openid.net/specs/openid-connect-core-1_0.html#IDTokenValidation
     */
    public function createClaimCheckerManager($check = []): ClaimCheckerManager
    {
        return ClaimCheckerManager::create([
            new AudienceChecker($this->clientMetadata->id()),
            new ExpirationTimeChecker(),
            new IssuedAtChecker(),
            new NotBeforeChecker(),
            new NonceChecker($check['nonce'] ?? null),
        ]);
    }

    /**
     * @return HeaderCheckerManager
     */
    public function createHeaderCheckerManager(): HeaderCheckerManager
    {
        $tokenTypesSupport = [new JWSTokenSupport()];

        if (null !== $this->providerMetadata->get('id_token_encryption_alg_values_supported')) {
            $tokenTypesSupport[] = new JWETokenSupport();
        }

        return HeaderCheckerManager::create([
            new AlgorithmChecker($this->resolveAlgorithms()),
            new IssuerChecker($this->providerMetadata->require('issuer')),
        ], $tokenTypesSupport);
    }

    /**
     * @return JWSBuilder
     */
    public function createJwsBuilder(): JWSBuilder
    {
        return new JWSBuilder(
            null,
            $this->createAlgorithmManager()
        );
    }

    /**
     * @return JWSLoader
     */
    public function createJwsLoader(): JWSLoader
    {
        return new JWSLoader(
            $this->createJwsSerializerManager(),
            $this->createJwsVerifier(),
            $this->createHeaderCheckerManager()
        );
    }

    /**
     * @return JWSSerializerManager
     * @todo Serializer must be editable
     */
    public function createJwsSerializerManager(): JWSSerializerManager
    {
        return JWSSerializerManager::create([
            new CompactSerializer(),
        ]);
    }

    /**
     * @return JWSVerifier
     */
    public function createJwsVerifier(): JWSVerifier
    {
        return new JWSVerifier($this->createAlgorithmManager());
    }

    /**
     * @param array<int, mixed> $alg
     * @return static
     */
    public function withAlgorithm(...$alg)
    {
        if (is_array($alg[0])) {
            $alg = $alg[0];
        }

        $this->algorithms = $alg;

        return $this;
    }

    private function resolveAlgorithms(): array
    {
        return array_unique(array_merge(
            $this->providerMetadata->require('id_token_signing_alg_values_supported'),
            $this->providerMetadata->get('id_token_encryption_alg_values_supported', []),
            $this->providerMetadata->get('id_token_encryption_enc_values_supported', []),
            $this->algorithms
        ));
    }
}
