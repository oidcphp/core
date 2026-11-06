<?php

namespace OpenIDConnect\Jwt\Verifiers;

use Exception;
use Jose\Component\Checker\AudienceChecker;
use Jose\Component\Checker\ClaimCheckerManager;
use Jose\Component\Checker\ExpirationTimeChecker;
use Jose\Component\Checker\IssuedAtChecker;
use Jose\Component\Checker\IssuerChecker;
use Jose\Component\Core\JWKSet;
use Jose\Component\Core\Util\JsonConverter;
use Jose\Component\Signature\JWS;
use Jose\Component\Signature\Serializer\CompactSerializer;
use OpenIDConnect\Config;
use OpenIDConnect\Contracts\JwtVerifier;
use OpenIDConnect\Exceptions\OpenIDProviderException;
use OpenIDConnect\Exceptions\RelyingPartyException;
use OpenIDConnect\Jwt\Checkers\NonceChecker;
use OpenIDConnect\Jwt\ClaimCheckerManagerBuilder;
use OpenIDConnect\Jwt\Claims;
use OpenIDConnect\Jwt\Factory as JwtFactory;
use OpenIDConnect\Traits\ClockTolerance;
use OpenIDConnect\Traits\ConfigAwareTrait;
use UnexpectedValueException;

/**
 * @link https://openid.net/specs/openid-connect-core-1_0.html#IDTokenValidation
 */
class IdTokenVerifier implements JwtVerifier
{
    use ClockTolerance;
    use ConfigAwareTrait;

    public function __construct(Config $config, $clockTolerance = 10)
    {
        $this->config = $config;
        $this->clockTolerance = $clockTolerance;
    }

    public function verify(string $token, $extraMandatoryClaims = [], $check = []): Claims
    {
        $factory = new JwtFactory($this->config);

        $loader = $factory->createJwsLoader();

        $signature = null;

        try {
            $jws = $loader->loadAndVerifyWithKeySet(
                $token,
                JWKSet::createFromKeyData($this->config->providerMetadata()->jwkSet()->toArray()),
                $signature
            );
        } catch (Exception $e) {
            throw new OpenIDProviderException('Receive an invalid ID token: ' . $e->getMessage(), 0, $e);
        }

        return $this->verifyClaims($jws, $extraMandatoryClaims, $check);
    }

    /**
     * Verify an ID Token which is not signed (`alg` is `none`).
     *
     * This method MUST only be used for an ID Token received directly from the Token Endpoint.
     *
     * @link https://openid.net/specs/openid-connect-core-1_0.html#IDToken
     * @link https://openid.net/specs/openid-connect-core-1_0.html#IDTokenValidation
     * @link https://openid.net/specs/openid-connect-registration-1_0.html#ClientMetadata
     */
    public function verifyUnsigned(string $token, $extraMandatoryClaims = [], $check = []): Claims
    {
        // Core 2 (ID Token): `none` is only acceptable when the client explicitly registered
        // `id_token_signed_response_alg` as `none`, so reject the token otherwise.
        if ('none' !== $this->config->getClientMetadata('id_token_signed_response_alg')) {
            throw new RelyingPartyException(
                "Unsigned ID token is not accepted, because the client did not register "
                . "'id_token_signed_response_alg' as 'none'"
            );
        }

        try {
            $jws = (new CompactSerializer())->unserialize($token);

            $header = $jws->getSignature(0)->getProtectedHeader();
        } catch (Exception $e) {
            throw new OpenIDProviderException('Receive an invalid ID token: ' . $e->getMessage(), 0, $e);
        }

        if (($header['alg'] ?? null) !== 'none' || '' !== $jws->getSignature(0)->getSignature()) {
            throw new OpenIDProviderException(
                "Receive an invalid ID token: expected an unsigned ID token with 'alg' as 'none'"
            );
        }

        // Core 3.1.3.7 step 6: the signature check is replaced by TLS server validation, because the ID Token
        // is received directly from the Token Endpoint. The other validation steps are still applied.
        return $this->verifyClaims($jws, $extraMandatoryClaims, $check);
    }

    private function verifyClaims(JWS $jws, $extraMandatoryClaims, $check): Claims
    {
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

        return Claims::createFromJwt($jws);
    }

    /**
     * @param array $check
     * @return ClaimCheckerManager
     */
    private function createClaimCheckerManager($check = []): ClaimCheckerManager
    {
        $builder = new ClaimCheckerManagerBuilder();

        // 2.  The Issuer Identifier for the OpenID Provider (which is
        //     typically obtained during Discovery) MUST exactly match
        //     the value of the iss (issuer) Claim.
        $builder->add(IssuerChecker::class, [$this->config->requireProviderMetadata('issuer')]);

        // 3.  The Client MUST validate that the aud (audience) Claim contains
        //     its client_id value registered at the Issuer identified by the
        //     iss (issuer) Claim as an audience. The aud (audience) Claim MAY
        //     contain an array with more than one element.
        //
        //     The ID Token MUST be rejected if the ID Token does not list the
        //     Client as a valid audience, or if it contains additional
        //     audiences not trusted by the Client.
        $builder->add(AudienceChecker::class, $this->config->requireClientMetadata('client_id'));

        // 9.  The current time MUST be before the time represented by the exp
        //     Claim.
        $builder->add(ExpirationTimeChecker::class, $this->clockTolerance());

        // 10. The iat Claim can be used to reject tokens that were issued too
        //     far away from the current time, limiting the amount of time that
        //     nonces need to be stored to prevent attacks. The acceptable
        //     range is Client specific.
        $builder->add(IssuedAtChecker::class, $this->clockTolerance());

        // 11. If a nonce value was sent in the Authentication Request, a nonce
        //     Claim MUST be present and its value checked to verify that it is
        //     the same value as the one that was sent in the Authentication
        //     Request.
        //
        //     The Client SHOULD check the nonce value for replay attacks. The
        //     precise method for detecting replay attacks is Client specific.
        if (isset($check['nonce'])) {
            $builder->add(NonceChecker::class, $check['nonce']);
        }

        return $builder->build();
    }
}
