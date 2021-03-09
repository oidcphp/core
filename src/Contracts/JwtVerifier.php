<?php

namespace OpenIDConnect\Contracts;

use OpenIDConnect\Jwt\Claims;

/**
 * Verify JWT token using Claims class
 *
 * Such as ID Token, Logout Token, etc.
 */
interface JwtVerifier
{
    /**
     * @param string $token
     * @param array $extraMandatoryClaims
     * @param array $check
     * @return Claims
     */
    public function verify(string $token, $extraMandatoryClaims = [], $check = []): Claims;
}
