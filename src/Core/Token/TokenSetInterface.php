<?php

namespace OpenIDConnect\Core\Token;

use OpenIDConnect\Core\Claims;
use OpenIDConnect\OAuth2\Token\TokenSetInterface as BaseTokenSetInterface;

/**
 * The token set interface for OpenID Connect flow
 */
interface TokenSetInterface extends BaseTokenSetInterface
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
     * Verified claim from ID token string
     *
     * @param array $extraMandatoryClaims
     * @param array $check
     * @return Claims
     * @link https://openid.net/specs/openid-connect-core-1_0.html#IDToken
     */
    public function idTokenClaims($extraMandatoryClaims = [], $check = []): Claims;

    /**
     * The raw ID token string
     *
     * @return string
     */
    public function idToken(): ?string;
}
