<?php

declare(strict_types=1);

namespace OpenIDConnect;

use Illuminate\Support\Facades\Facade;
use Jose\Component\Core\Util\JsonConverter;
use Jose\Component\Signature\JWS;

/**
 * Verified JWT
 *
 * @see https://openid.net/specs/openid-connect-core-1_0.html#IDToken
 */
class IdToken
{
    /**
     * @var array
     */
    private $claims;

    /**
     * @param JWS $jws
     * @return static
     */
    public static function createFromJWS(JWS $jws)
    {
        $payload = $jws->getPayload();

        if (null === $payload) {
            return new static();
        }

        return new static(JsonConverter::decode($payload));
    }

    /**
     * @param array $claims
     */
    public function __construct(array $claims = [])
    {
        $this->claims = $claims;
    }

    /**
     * Return all claims
     *
     * @return array
     */
    public function all(): array
    {
        return $this->claims;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function claim(string $key, $default = null)
    {
        if (!isset($this->claims[$key])) {
            return $default;
        }

        return $this->claims[$key];
    }

    /**
     * Issuer Identifier for the Issuer of the response
     *
     * @return string
     */
    public function iss(): string
    {
        return $this->claim('iss');
    }

    /**
     * Subject Identifier
     *
     * @return string
     */
    public function sub(): string
    {
        return $this->claim('sub');
    }

    /**
     * Audience(s) that this ID Token is intended for
     *
     * @return string|array
     */
    public function aud()
    {
        return $this->claim('aud');
    }

    /**
     * Expiration time on or after which the ID Token MUST NOT be accepted for processing
     *
     * @return int
     */
    public function exp(): int
    {
        return $this->claim('exp');
    }

    /**
     * Time at which the JWT was issued
     *
     * @return int
     */
    public function iat(): int
    {
        return $this->claim('iat');
    }

    /**
     * Time when the End-User authentication occurred
     *
     * @return int
     */
    public function authTime(): ?int
    {
        return $this->claim('auth_time');
    }

    /**
     * String value used to associate a Client session with an ID Token, and to mitigate replay attacks
     *
     * @return string
     */
    public function nonce(): ?string
    {
        return $this->claim('nonce');
    }

    /**
     * Authentication Context Class Reference
     *
     * @return string
     */
    public function acr(): ?string
    {
        return $this->claim('acr');
    }

    /**
     * Authentication Methods References
     *
     * @return string
     */
    public function amr(): ?string
    {
        return $this->claim('amr');
    }

    /**
     * Authorized party - the party to which the ID Token was issued
     *
     * @return string
     */
    public function azp(): ?string
    {
        return $this->claim('azp');
    }
}
