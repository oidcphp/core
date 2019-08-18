<?php

namespace OpenIDConnect\Token;

use InvalidArgumentException;
use JsonSerializable;

class TokenSet implements JsonSerializable, TokenSetInterface
{
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

        $this->values = array_diff_key($parameters, array_flip([
            'access_token',
            'expires_in',
            'id_token',
            'refresh_token',
            'scope',
        ]));
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
        return $this->parameters['expires_in'] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function idToken(): string
    {
        return $this->parameters['id_token'] ?? null;
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
        return $this->parameters['refresh_token'] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function scope(): ?array
    {
        if (!isset($this->parameters['scope'])) {
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

        if (!isset($this->values[$key])) {
            return $default;
        }

        return $this->values[$key];
    }
}
