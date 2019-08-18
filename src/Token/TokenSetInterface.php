<?php

namespace OpenIDConnect\Token;

/**
 * The token set interface for OpenID Connect flow
 */
interface TokenSetInterface
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
     * @see https://openid.net/specs/openid-connect-core-1_0.html#IDToken
     * @return string
     */
    public function idToken();

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
