<?php

declare(strict_types=1);

namespace OpenIDConnect\Contracts;

use OpenIDConnect\PkceCodeVerifier;

/**
 * The values which the Relying Party binds to one Authentication Request, and needs again to handle the response.
 *
 * - {@see \OpenIDConnect\AuthenticationRequestContext} is the default, `state` and `nonce` are always present.
 * - {@see \OpenIDConnect\UnsafeAuthenticationRequestContext} is for the flows which do not send them.
 *
 * A `null` value of `state` or `nonce` means it was not sent, so it is not validated.
 */
interface AuthenticationRequestContextInterface
{
    public function redirectUri(): string;

    public function state(): ?string;

    public function nonce(): ?string;

    public function codeVerifier(): ?PkceCodeVerifier;
}
