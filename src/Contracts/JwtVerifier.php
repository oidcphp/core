<?php

namespace OpenIDConnect\Contracts;

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
     * @return void
     */
    public function verify(string $token, $extraMandatoryClaims = [], $check = []): void;
}
