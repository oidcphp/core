<?php

namespace OpenIDConnect\Core\Token\Concerns;

use Jose\Component\Core\Util\JsonConverter;
use OpenIDConnect\Core\Claims;
use OpenIDConnect\Core\Exceptions\RelyingPartyException;
use OpenIDConnect\Core\Jwt\JwtFactory;
use RangeException;
use UnexpectedValueException;

trait IdToken
{
    /**
     * @var Claims
     */
    private $claims;

    public function idTokenClaims($extraMandatoryClaims = [], $check = []): Claims
    {
        if (null !== $this->claims) {
            return $this->claims;
        }

        $token = $this->idToken();

        if (null === $token) {
            throw new RangeException('No ID token');
        }

        /** @var JwtFactory $jwtFactory */
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

        if ($this->has('nonce')) {
            $check['nonce'] = $this->values('nonce');
        }

        $claimCheckerManager = $jwtFactory->createClaimCheckerManager($check);

        try {
            $mandatoryClaims = array_unique(array_merge(static::REQUIRED_CLAIMS, $extraMandatoryClaims));

            $claimCheckerManager->check(JsonConverter::decode($payload), $mandatoryClaims);
        } catch (\Exception $e) {
            throw new RelyingPartyException('Receive an invalid ID token: ' . $this->idToken(), 0, $e);
        }

        return $this->claims = Claims::createFromJWS($jws);
    }
}
