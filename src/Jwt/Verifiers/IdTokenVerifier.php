<?php

namespace OpenIDConnect\Jwt\Verifiers;

use Exception;
use Jose\Component\Checker\AudienceChecker;
use Jose\Component\Checker\ClaimCheckerManager;
use Jose\Component\Checker\ExpirationTimeChecker;
use Jose\Component\Checker\IssuedAtChecker;
use Jose\Component\Checker\IssuerChecker;
use Jose\Component\Checker\NotBeforeChecker;
use Jose\Component\Core\JWKSet;
use Jose\Component\Core\Util\JsonConverter;
use OpenIDConnect\Config;
use OpenIDConnect\Contracts\JwtVerifier;
use OpenIDConnect\Exceptions\OpenIDProviderException;
use OpenIDConnect\Exceptions\RelyingPartyException;
use OpenIDConnect\Jwt\Checkers\NonceChecker;
use OpenIDConnect\Jwt\ClaimCheckerManagerBuilder;
use OpenIDConnect\Jwt\JwtFactory;
use OpenIDConnect\Traits\ClockTolerance;
use UnexpectedValueException;

/**
 * @see https://openid.net/specs/openid-connect-core-1_0.html#IDTokenValidation
 */
class IdTokenVerifier implements JwtVerifier
{
    use ClockTolerance;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var JwtFactory
     */
    private $factory;

    public function __construct(Config $config, JwtFactory $factory, $clockTolerance = 10)
    {
        $this->config = $config;
        $this->factory = $factory;
        $this->clockTolerance = $clockTolerance;
    }

    public function verify(string $token, $extraMandatoryClaims = [], $check = []): void
    {
        $loader = $this->factory->createJwsLoader();

        $signature = null;

        try {
            $jws = $loader->loadAndVerifyWithKeySet(
                $token,
                JWKSet::createFromKeyData($this->config->providerMetadata()->jwkSet()->toArray()),
                $signature
            );
        } catch (Exception $e) {
            throw new OpenIDProviderException('Receive an invalid ID token: ' . $token, 0, $e);
        }

        $payload = $jws->getPayload();

        if (null === $payload) {
            throw new UnexpectedValueException('JWT has no payload');
        }

        $claimCheckerManager = $this->createClaimCheckerManager($check);

        try {
            $mandatoryClaims = array_unique(array_merge([
                'aud',
                'exp',
                'iat',
                'iss',
                'sub',
            ], $extraMandatoryClaims));

            $claimCheckerManager->check(JsonConverter::decode($payload), $mandatoryClaims);
        } catch (Exception $e) {
            throw new RelyingPartyException('Relying party info is invalid: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param array $check
     * @return ClaimCheckerManager
     * @link https://openid.net/specs/openid-connect-core-1_0.html#IDTokenValidation
     */
    private function createClaimCheckerManager($check = []): ClaimCheckerManager
    {
        return (new ClaimCheckerManagerBuilder())
            ->add(AudienceChecker::class, $this->config->requireClientMetadata('client_id'))
            ->add(IssuerChecker::class, [$this->config->requireProviderMetadata('issuer')])
            ->add(ExpirationTimeChecker::class, $this->clockTolerance())
            ->add(IssuedAtChecker::class, $this->clockTolerance())
            ->add(NotBeforeChecker::class, $this->clockTolerance())
            ->add(NonceChecker::class, $check['nonce'] ?? null)
            ->build();
    }
}
