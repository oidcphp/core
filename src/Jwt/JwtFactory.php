<?php

namespace OpenIDConnect\Jwt;

use Jose\Component\Checker\AlgorithmChecker;
use Jose\Component\Checker\AudienceChecker;
use Jose\Component\Checker\ClaimCheckerManager;
use Jose\Component\Checker\ExpirationTimeChecker;
use Jose\Component\Checker\HeaderCheckerManager;
use Jose\Component\Checker\IssuedAtChecker;
use Jose\Component\Checker\NotBeforeChecker;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Encryption\JWETokenSupport;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\JWSLoader;
use Jose\Component\Signature\JWSTokenSupport;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use OpenIDConnect\Contracts\ConfigInterface;
use OpenIDConnect\Jwt\Checker\NonceChecker;
use OpenIDConnect\Traits\ConfigAwareTrait;

class JwtFactory
{
    use AlgorithmFactoryTrait;
    use ConfigAwareTrait;

    /**
     * Addition algorithms
     *
     * @var array
     */
    private $algorithms = [];

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @return AlgorithmManager
     */
    public function createAlgorithmManager(): AlgorithmManager
    {
        return new AlgorithmManager(
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
        return new ClaimCheckerManager([
            new AudienceChecker($this->config->requireClientMetadata('client_id')),
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

        if (null !== $this->config->getProviderMetadata('id_token_encryption_alg_values_supported')) {
            $tokenTypesSupport[] = new JWETokenSupport();
        }

        return new HeaderCheckerManager([
            new AlgorithmChecker($this->resolveAlgorithms()),
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
        return new JWSSerializerManager([
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
        $providerMetadata = $this->config->providerMetadata();

        return array_unique(array_merge(
            $providerMetadata->require('id_token_signing_alg_values_supported'),
            $providerMetadata->get('id_token_encryption_alg_values_supported', []),
            $providerMetadata->get('id_token_encryption_enc_values_supported', []),
            $this->algorithms
        ));
    }
}
