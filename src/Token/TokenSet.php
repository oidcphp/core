<?php

namespace OpenIDConnect\Token;

use InvalidArgumentException;

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
     * @var array
     */
    private $parameters;

    /**
     * @var array
     */
    private $values;

    /**
     * @param array $parameters An array from token endpoint response body
     * @throws InvalidArgumentException
     */
    public function __construct(array $parameters = [])
    {
        if (empty($parameters['access_token'])) {
            throw new InvalidArgumentException('Required "access_token" but not passed');
        }

        $this->parameters = $parameters;

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

    /**
     * {@inheritDoc}
     */
    public function idToken(): ?string
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
}
