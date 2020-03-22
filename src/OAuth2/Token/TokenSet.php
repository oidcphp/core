<?php

declare(strict_types=1);

namespace OpenIDConnect\OAuth2\Token;

use OpenIDConnect\OAuth2\Traits\ParameterTrait;

class TokenSet implements TokenSetInterface
{
    use ParameterTrait;

    /**
     * @param array<mixed> $parameters An array from token endpoint response body
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @inheritDoc
     */
    public function accessToken(): string
    {
        return $this->require('access_token');
    }

    /**
     * @inheritDoc
     */
    public function expiresIn(): ?int
    {
        return $this->get('expires_in');
    }

    /**
     * @inheritDoc
     */
    public function refreshToken(): ?string
    {
        return $this->get('refresh_token');
    }

    /**
     * @inheritDoc
     */
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
