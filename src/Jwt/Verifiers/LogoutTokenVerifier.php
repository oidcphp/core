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
use OpenIDConnect\Jwt\Checkers\NonceNotContainChecker;
use OpenIDConnect\Jwt\ClaimCheckerManagerBuilder;
use OpenIDConnect\Jwt\Claims;
use OpenIDConnect\Jwt\Factory as JwtFactory;
use OpenIDConnect\Traits\ClockTolerance;
use OpenIDConnect\Traits\ConfigAwareTrait;
use Psr\Clock\ClockInterface;
use UnexpectedValueException;

/**
 * @see https://openid.net/specs/openid-connect-backchannel-1_0.html#Validation
 */
class LogoutTokenVerifier implements JwtVerifier
{
    use ClockTolerance;
    use ConfigAwareTrait;

    public function __construct(Config $config, ClockInterface $clock, $clockTolerance = 10)
    {
        $this->config = $config;
        $this->clock = $clock;
        $this->clockTolerance = $clockTolerance;
    }

    public function verify(string $token, $extraMandatoryClaims = [], $check = []): Claims
    {
        $factory = new JwtFactory($this->config);

        $loader = $factory->createJwsLoader();

        $signature = null;

        // 2.  Validate the Logout Token signature in the same way that an ID
        //     Token signature is validated, with the following refinements.
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

        // 4. Verify that the Logout Token contains a sub Claim, a sid Claim, or both.
        if (empty($claims['sub']) && empty($claims['sid'])) {
            throw new RelyingPartyException('No sub and sid both in claims');
        }

        return Claims::createFromJwt($jws);
    }

    /**
     * @return ClaimCheckerManager
     */
    private function createClaimCheckerManager(): ClaimCheckerManager
    {
        $builder = new ClaimCheckerManagerBuilder();

        // 3.  Validate the iss, aud, and iat Claims in the same way they are
        //     validated in ID Tokens.
        $builder->add(AudienceChecker::class, $this->config->requireClientMetadata('client_id'))
            ->add(IssuerChecker::class, [$this->config->requireProviderMetadata('issuer')])
            ->add(IssuedAtChecker::class, $this->clock, $this->clockTolerance());

        // 5.  Verify that the Logout Token contains an events Claim whose
        //     value is JSON object containing the member name
        //     http://schemas.openid.net/event/backchannel-logout.
        $builder->add(BackChannelLogoutEventsChecker::class);

        // 6.  Verify that the Logout Token does not contain a nonce Claim.
        $builder->add(NonceNotContainChecker::class);

        return $builder->build();
    }
}
