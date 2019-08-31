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
use OpenIDConnect\Metadata\ProviderMetadata;

class Factory
{
    use AlgorithmFactoryTrait;

    /**
     * Addition algorithms
     *
     * @var array
     */
    private $algorithms = [];

    /**
     * @var ProviderMetadata
     */
    private $providerMetadata;

    public function __construct(ProviderMetadata $providerMetadata)
    {
        $this->providerMetadata = $providerMetadata;
    }

    /**
     * @return AlgorithmManager
     */
    public function createAlgorithmManager(): AlgorithmManager
    {
        return AlgorithmManager::create(
            $this->createSignatureAlgorithms($this->resolveAlgorithms())
        );
    }

    /**
     * @return ClaimCheckerManager
     */
    public function createClaimCheckerManager(): ClaimCheckerManager
    {
        return ClaimCheckerManager::create([
            new ExpirationTimeChecker(),
            new IssuedAtChecker(),
            new NotBeforeChecker(),
        ]);
    }

    /**
     * @return HeaderCheckerManager
     */
    public function createHeaderCheckerManager(): HeaderCheckerManager
    {
        $tokenTypesSupport = [new JWSTokenSupport()];

        if (null !== $this->providerMetadata->idTokenEncryptionAlgValuesSupported()) {
            $tokenTypesSupport[] = new JWETokenSupport();
        }

        return HeaderCheckerManager::create([
            new AlgorithmChecker($this->resolveAlgorithms()),
            new IssuerChecker($this->providerMetadata->issuer()),
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

    private function resolveAlgorithms()
    {
        return array_unique(array_merge(
            $this->providerMetadata->idTokenAlgValuesSupported(),
            $this->algorithms
        ));
    }
}
