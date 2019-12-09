<?php

namespace OpenIDConnect\Core\Token\Concerns;

use Jose\Component\Core\JWKSet;
use Jose\Component\Core\Util\JsonConverter;
use OpenIDConnect\Core\Claims;
use OpenIDConnect\Core\Exceptions\RelyingPartyException;
use OpenIDConnect\Core\Jwt\JwtFactory;
use OpenIDConnect\OAuth2\Metadata\ClientInformationAwaitTrait;
use OpenIDConnect\OAuth2\Metadata\ProviderMetadataAwaitTrait;
use RangeException;
use UnexpectedValueException;

trait IdToken
{
    use ClientInformationAwaitTrait;
    use ProviderMetadataAwaitTrait;

    /**
     * @var Claims
     */
    private $claims;

    /**
     * @var JwtFactory
     */
    private $jwtFactory;

    public function idTokenClaims($extraMandatoryClaims = [], $check = []): Claims
    {
        if (null !== $this->claims) {
            return $this->claims;
        }

        $token = $this->idToken();

        if (null === $token) {
            throw new RangeException('No ID token');
        }

        $loader = $this->jwtFactory->createJwsLoader();

        $signature = null;

        $jws = $loader->loadAndVerifyWithKeySet(
            $token,
            JWKSet::createFromKeyData($this->providerMetadata->jwkSet()->toArray()),
            $signature
        );

        $payload = $jws->getPayload();

        if (null === $payload) {
            throw new UnexpectedValueException('JWT has no payload');
        }

        if ($this->has('nonce')) {
            $check['nonce'] = $this->get('nonce');
        }

        $claimCheckerManager = $this->jwtFactory->createClaimCheckerManager($check);

        try {
            $mandatoryClaims = array_unique(array_merge(static::REQUIRED_CLAIMS, $extraMandatoryClaims));

            $claimCheckerManager->check(JsonConverter::decode($payload), $mandatoryClaims);
        } catch (\Exception $e) {
            throw new RelyingPartyException('Receive an invalid ID token: ' . $this->idToken(), 0, $e);
        }

        return $this->claims = Claims::createFromJWS($jws);
    }

    /**
     * @param JwtFactory $jwtFactory
     * @return static
     */
    public function setJwtFactory(JwtFactory $jwtFactory)
    {
        $this->jwtFactory = $jwtFactory;

        return $this;
    }
}
