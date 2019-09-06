<?php

namespace OpenIDConnect\Token\Concerns;

use Jose\Component\Core\Util\JsonConverter;
use OpenIDConnect\Claims;
use OpenIDConnect\Exceptions\RelyingPartyException;
use RangeException;
use UnexpectedValueException;

trait IdToken
{
    /**
     * @var Claims
     */
    private $claims;

    public function idTokenClaims($extraMandatoryClaims = []): Claims
    {
        if (null !== $this->claims) {
            return $this->claims;
        }

        $token = $this->idToken();

        if (null === $token) {
            throw new RangeException('No ID token');
        }

        $jwtFactory = $this->createJwtFactory();

        $loader = $jwtFactory->createJwsLoader();

        $signature = null;

        $jws = $loader->loadAndVerifyWithKeySet(
            $token,
            $this->providerMetadata->jwkSet(),
            $signature
        );

        $payload = $jws->getPayload();

        if (null === $payload) {
            throw new UnexpectedValueException('JWT has no payload');
        }

        $claimCheckerManager = $jwtFactory->createClaimCheckerManager();

        try {
            $mandatoryClaims = array_unique(array_merge(static::REQUIRED_CLAIMS, $extraMandatoryClaims));

            $claimCheckerManager->check(JsonConverter::decode($payload), $mandatoryClaims);
        } catch (\Exception $e) {
            throw new RelyingPartyException('Receive an invalid ID token: ' . $this->idToken(), 0, $e);
        }

        return $this->claims = Claims::createFromJWS($jws);
    }
}
