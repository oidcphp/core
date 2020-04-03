<?php

declare(strict_types=1);

namespace OpenIDConnect\Token;

use DomainException;
use JsonSerializable;
use OpenIDConnect\Core\Claims;

/**
 * The token set interface for OpenID Connect flow
 */
interface TokenSetInterface extends JsonSerializable
{
    /**
     * @see https://openid.net/specs/openid-connect-core-1_0.html#IDToken
     */
    public const REQUIRED_CLAIMS = [
        'aud',
        'exp',
        'iat',
        'iss',
        'sub',
    ];

    /**
     * @see https://tools.ietf.org/html/rfc6749#section-1.4
     * @return string
     */
    public function accessToken(): string;

    /**
     * @see https://tools.ietf.org/html/rfc6749#section-5.1
     * @return int|null
     */
    public function expiresIn(): ?int;

    /**
     * Returns additional vendor values stored in the token.
     *
     * @param string|int $key Given null will get all value
     * @param mixed $default Return default when the key is not found
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * @param string|int $key
     * @return bool
     */
    public function has($key): bool;

    /**
     * The raw ID token string
     *
     * @return string
     */
    public function idToken(): ?string;

    /**
     * Verified claim from ID token string
     *
     * @param array $extraMandatoryClaims
     * @param array $check
     * @return Claims
     * @link https://openid.net/specs/openid-connect-core-1_0.html#IDToken
     */
    public function idTokenClaims($extraMandatoryClaims = [], $check = []): Claims;

    /**
     * @see https://tools.ietf.org/html/rfc6749#section-1.5
     * @return string|null
     */
    public function refreshToken(): ?string;

    /**
     * Require key and return the value
     *
     * @param string|int $key Given null will get all value
     * @return mixed
     * @throws DomainException Throw when the key is not exist
     */
    public function require($key);

    /**
     * @see https://tools.ietf.org/html/rfc6749#section-5.1
     * @return array<string>|null
     */
    public function scope(): ?array;
}
