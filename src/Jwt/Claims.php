<?php

declare(strict_types=1);

namespace OpenIDConnect\Jwt;

use InvalidArgumentException;
use Jose\Component\Core\JWT;
use Jose\Component\Core\Util\JsonConverter;
use ParagonIE\ConstantTime\Base64UrlSafe;

/**
 * JWT Claims
 *
 * @see https://tools.ietf.org/html/rfc7519#section-4
 * @see https://openid.net/specs/openid-connect-core-1_0.html#IDToken
 * @see https://openid.net/specs/openid-connect-backchannel-1_0.html#LogoutToken
 */
class Claims
{
    /**
     * @var array
     */
    private $claims;

    /**
     * @param string $token
     * @return mixed
     */
    public static function createFromJwsString(string $token): Claims
    {
        $parts = explode('.', $token);

        if (3 !== count($parts)) {
            throw new InvalidArgumentException('Unsupported input');
        }

        $claims = json_decode(
            Base64UrlSafe::decode($parts[1]),
            true,
            512,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        return new self($claims);
    }

    /**
     * @param JWT $jwt
     * @return Claims
     */
    public static function createFromJwt(JWT $jwt): Claims
    {
        $payload = $jwt->getPayload();

        if (null === $payload) {
            return new self();
        }

        return new self(JsonConverter::decode($payload));
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
    public function sub(): ?string
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

    /**
     * @return array
     */
    public function events(): array
    {
        return $this->claim('events');
    }

    /**
     * Unique identifier for the token
     *
     * @return string
     */
    public function jti(): string
    {
        return $this->claim('jti');
    }

    /**
     * Session ID - String identifier for a Session.
     *
     * @return string
     */
    public function sid(): ?string
    {
        return $this->claim('sid');
    }
}
