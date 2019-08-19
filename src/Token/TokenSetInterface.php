<?php

namespace OpenIDConnect\Token;

use JsonSerializable;

/**
 * The token set interface for OpenID Connect flow
 */
interface TokenSetInterface extends JsonSerializable
{
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
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * @return bool
     */
    public function hasExpiresIn(): bool;

    /**
     * @return bool
     */
    public function hasIdToken(): bool;

    /**
     * @return bool
     */
    public function hasRefreshToken(): bool;

    /**
     * @return bool
     */
    public function hasScope(): bool;

    /**
     * @see https://openid.net/specs/openid-connect-core-1_0.html#IDToken
     * @return string
     */
    public function idToken(): ?string;

    /**
     * @see https://tools.ietf.org/html/rfc6749#section-1.5
     * @return string|null
     */
    public function refreshToken(): ?string;

    /**
     * @see https://tools.ietf.org/html/rfc6749#section-5.1
     * @return array|null
     */
    public function scope(): ?array;

    /**
     * Returns additional vendor values stored in the token.
     *
     * @param string|null $key Given null will get all value
     * @param mixed $default Return default when the key is not found
     * @return mixed
     */
    public function values(string $key = null, $default = null);
}
