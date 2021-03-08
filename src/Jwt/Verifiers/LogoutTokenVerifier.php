<?php

namespace OpenIDConnect\Jwt\Verifiers;

use Exception;
use Jose\Component\Checker\AudienceChecker;
use Jose\Component\Checker\ClaimCheckerManager;
use Jose\Component\Checker\IssuedAtChecker;
use Jose\Component\Checker\IssuerChecker;
use Jose\Component\Core\JWKSet;
use Jose\Component\Core\Util\JsonConverter;
use OpenIDConnect\Config;
use OpenIDConnect\Contracts\JwtVerifier;
use OpenIDConnect\Exceptions\OpenIDProviderException;
use OpenIDConnect\Exceptions\RelyingPartyException;
use OpenIDConnect\Jwt\Checkers\BackChannelLogoutEventsChecker;
use OpenIDConnect\Jwt\ClaimCheckerManagerBuilder;
use OpenIDConnect\Jwt\JwtFactory;
use OpenIDConnect\Traits\ClockTolerance;
use UnexpectedValueException;

/**
 * @see https://openid.net/specs/openid-connect-backchannel-1_0.html#Validation
 */
class LogoutTokenVerifier implements JwtVerifier
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

        $claimCheckerManager = $this->createClaimCheckerManager();

        try {
            $mandatoryClaims = array_unique(array_merge([
                'events',
                'aud',
                'iat',
                'iss',
            ], $extraMandatoryClaims));

            $claims = JsonConverter::decode($payload);

            $claimCheckerManager->check($claims, $mandatoryClaims);
        } catch (Exception $e) {
            throw new RelyingPartyException('Relying party info is invalid: ' . $e->getMessage(), 0, $e);
        }

        if (array_key_exists('nonce', $claims)) {
            throw new OpenIDProviderException('Logout token should not contain nonce');
        }
    }

    /**
     * @param array $check
     * @return ClaimCheckerManager
     */
    private function createClaimCheckerManager(): ClaimCheckerManager
    {
        return (new ClaimCheckerManagerBuilder())
            ->add(AudienceChecker::class, $this->config->requireClientMetadata('client_id'))
            ->add(IssuerChecker::class, [$this->config->requireProviderMetadata('issuer')])
            ->add(IssuedAtChecker::class, $this->clockTolerance())
            ->add(BackChannelLogoutEventsChecker::class)
            ->build();
    }
}
