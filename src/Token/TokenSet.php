<?php

declare(strict_types=1);

namespace OpenIDConnect\Token;

use Exception;
use Jose\Component\Core\JWKSet;
use Jose\Component\Core\Util\JsonConverter;
use OpenIDConnect\Claims;
use OpenIDConnect\Contracts\ConfigAwareInterface;
use OpenIDConnect\Contracts\ConfigInterface;
use OpenIDConnect\Contracts\TokenSetInterface;
use OpenIDConnect\Exceptions\RelyingPartyException;
use OpenIDConnect\Jwt\JwtFactory;
use OpenIDConnect\Traits\ClockTolerance;
use OpenIDConnect\Traits\ConfigAwareTrait;
use OpenIDConnect\Traits\ParameterTrait;
use RangeException;
use UnexpectedValueException;

class TokenSet implements ConfigAwareInterface, TokenSetInterface
{
    use ConfigAwareTrait;
    use ClockTolerance;
    use ParameterTrait;

    /**
     * @var Claims
     */
    private $claims;

    /**
     * @param ConfigInterface $config
     * @param array $parameters An array from token endpoint response body
     * @param int $clockTolerance
     */
    public function __construct(ConfigInterface $config, array $parameters, $clockTolerance = 10)
    {
        $this->setConfig($config);
        $this->clockTolerance = $clockTolerance;
        $this->parameters = $parameters;
    }

    public function accessToken(): string
    {
        return $this->require('access_token');
    }

    public function expiresIn(): ?int
    {
        return $this->get('expires_in');
    }

    public function idToken(): ?string
    {
        return $this->get('id_token');
    }

    public function idTokenClaims($extraMandatoryClaims = [], $check = []): Claims
    {
        if (null !== $this->claims) {
            return $this->claims;
        }

        $token = $this->idToken();

        if (null === $token) {
            throw new RangeException('No ID token');
        }

        $jwtFactory = new JwtFactory($this->config, $this->clockTolerance());

        $loader = $jwtFactory->createJwsLoader();

        $signature = null;

        $jws = $loader->loadAndVerifyWithKeySet(
            $token,
            JWKSet::createFromKeyData($this->config->providerMetadata()->jwkSet()->toArray()),
            $signature
        );

        $payload = $jws->getPayload();

        if (null === $payload) {
            throw new UnexpectedValueException('JWT has no payload');
        }

        if ($this->has('nonce')) {
            $check['nonce'] = $this->get('nonce');
        }

        $claimCheckerManager = $jwtFactory->createClaimCheckerManager($check);

        try {
            $mandatoryClaims = array_unique(array_merge(static::REQUIRED_CLAIMS, $extraMandatoryClaims));

            $claimCheckerManager->check(JsonConverter::decode($payload), $mandatoryClaims);
        } catch (Exception $e) {
            throw new RelyingPartyException('Receive an invalid ID token: ' . $this->idToken(), 0, $e);
        }

        return $this->claims = Claims::createFromJWS($jws);
    }

    public function refreshToken(): ?string
    {
        return $this->get('refresh_token');
    }

    public function scope(): ?array
    {
        if (!$this->has('scope')) {
            return null;
        }

        if (is_array($this->parameters['scope'])) {
            return $this->parameters['scope'];
        }

        return explode(' ', $this->parameters['scope']);
    }
}
