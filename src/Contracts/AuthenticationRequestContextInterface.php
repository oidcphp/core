<?php

declare(strict_types=1);

namespace OpenIDConnect\Contracts;

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
    /**
     * The `redirect_uri` of the Authentication Request. The Token Request MUST send the same value.
     */
    public function redirectUri(): string;

    /**
     * The `state` of the Authentication Request, or `null` when it was not sent.
     */
    public function state(): ?string;

    /**
     * The `nonce` of the Authentication Request, or `null` when it was not sent.
     */
    public function nonce(): ?string;

    /**
     * The `code_verifier` of PKCE, or `null` when PKCE is not used.
     */
    public function codeVerifier(): ?PkceCodeVerifierInterface;
}
