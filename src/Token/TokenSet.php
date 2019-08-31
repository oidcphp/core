<?php

namespace OpenIDConnect\Token;

use InvalidArgumentException;
use Jose\Component\Core\Util\JsonConverter;
use OpenIDConnect\Exceptions\RelyingPartyException;
use OpenIDConnect\IdToken;
use OpenIDConnect\Jwt\Factory;
use OpenIDConnect\Metadata\ProviderMetadata;
use RangeException;
use UnexpectedValueException;

class TokenSet implements TokenSetInterface
{
    public const DEFAULT_KEYS = [
        'access_token',
        'expires_in',
        'id_token',
        'refresh_token',
        'scope',
    ];

    /**
     * @var IdToken
     */
    private $idToken;

    /**
     * @var ProviderMetadata
     */
    private $providerMetadata;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var array
     */
    private $values;

    /**
     * @param array $parameters An array from token endpoint response body
     * @param ProviderMetadata $providerMetadata
     */
    public function __construct(array $parameters, ProviderMetadata $providerMetadata)
    {
        if (empty($parameters['access_token'])) {
            throw new InvalidArgumentException('Required "access_token" but not passed');
        }

        $this->parameters = $parameters;
        $this->providerMetadata = $providerMetadata;

        $this->values = array_diff_key($this->parameters, array_flip(self::DEFAULT_KEYS));
    }

    /**
     * {@inheritDoc}
     */
    public function accessToken(): string
    {
        return $this->parameters['access_token'] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function expiresIn(): int
    {
        return $this->hasExpiresIn() ? $this->parameters['expires_in'] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $key): bool
    {
        return isset($this->parameters[$key]);
    }

    /**
     * {@inheritDoc}
     */
    public function hasExpiresIn(): bool
    {
        return $this->has('expires_in');
    }

    /**
     * {@inheritDoc}
     */
    public function hasIdToken(): bool
    {
        return $this->has('id_token');
    }

    /**
     * {@inheritDoc}
     */
    public function hasRefreshToken(): bool
    {
        return $this->has('refresh_token');
    }

    /**
     * {@inheritDoc}
     */
    public function hasScope(): bool
    {
        return $this->has('scope');
    }

    public function idToken($extraMandatoryClaims = []): IdToken
    {
        if (null !== $this->idToken) {
            return $this->idToken;
        }

        $token = $this->idTokenRaw();

        if (null === $token) {
            throw new RangeException('No ID token');
        }

        $jwtFactory = $this->jwtFactory();

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
            throw new RelyingPartyException('Receive an invalid ID token: ' . $this->idTokenRaw(), 0, $e);
        }

        return $this->idToken = IdToken::createFromJWS($jws);
    }

    /**
     * {@inheritDoc}
     */
    public function idTokenRaw(): ?string
    {
        return $this->hasIdToken() ? $this->parameters['id_token'] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize()
    {
        return $this->parameters;
    }

    /**
     * {@inheritDoc}
     */
    public function refreshToken(): string
    {
        return $this->hasRefreshToken() ? $this->parameters['refresh_token'] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function scope(): ?array
    {
        if (!$this->hasScope()) {
            return null;
        }

        if (is_array($this->parameters['scope'])) {
            return $this->parameters['scope'];
        }

        return explode(' ', $this->parameters['scope']);
    }

    /**
     * {@inheritDoc}
     */
    public function values(string $key = null, $default = null)
    {
        if (null === $key) {
            return $this->values;
        }

        if (in_array($key, self::DEFAULT_KEYS, true)) {
            throw new InvalidArgumentException('Cannot use values() method to get default keys');
        }

        if (!isset($this->values[$key])) {
            return $default;
        }

        return $this->values[$key];
    }

    /**
     * @return Factory
     */
    private function jwtFactory(): Factory
    {
        return $this->providerMetadata->createJwtFactory();
    }
}
