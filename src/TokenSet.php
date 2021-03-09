<?php

declare(strict_types=1);

namespace OpenIDConnect;

use JsonSerializable;
use OpenIDConnect\Jwt\Claims;
use OpenIDConnect\Traits\ClockTolerance;
use OpenIDConnect\Traits\ConfigAwareTrait;
use OpenIDConnect\Traits\ParameterTrait;

class TokenSet implements JsonSerializable
{
    use ConfigAwareTrait;
    use ClockTolerance;
    use ParameterTrait;

    /**
     * @var Claims
     */
    private $claims;

    /**
     * @param Config $config
     * @param array $parameters An array from token endpoint response body
     * @param int $clockTolerance
     */
    public function __construct(Config $config, array $parameters, $clockTolerance = 10)
    {
        $this->setConfig($config);
        $this->parameters = $parameters;
        $this->clockTolerance = $clockTolerance;
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

    public function idTokenClaims(): Claims
    {
        return Claims::createFromJwsString($this->idToken());
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
