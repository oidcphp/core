<?php

declare(strict_types=1);

namespace OpenIDConnect\Token;

use OpenIDConnect\Contracts\TokenSetInterface;
use OpenIDConnect\Token\Concerns\IdToken;
use OpenIDConnect\Traits\ParameterTrait;

class TokenSet implements TokenSetInterface
{
    use IdToken;
    use ParameterTrait;

    /**
     * @param array<mixed> $parameters An array from token endpoint response body
     */
    public function __construct(array $parameters)
    {
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
