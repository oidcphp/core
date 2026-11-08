<?php

declare(strict_types=1);

namespace OpenIDConnect;

use OpenIDConnect\Contracts\AuthenticationRequestContextInterface;

/**
 * The values the Relying Party binds to one Authentication Request, and needs again when the response comes back.
 *
 * It is NOT the Authentication Request itself, so it does not hold request parameters such as `scope` or `prompt`.
 * It is a plain data object, the values are mapped to the properties as given. Only the values which must be kept
 * until the Authentication Response is handled are allowed:
 *
 * - `state`: used to validate the response, it is not a parameter of the Token Request.
 * - `nonce`: used to validate the ID Token, it is not a parameter of the Token Request.
 * - `redirectUri`: it MUST be sent to the Token Endpoint with the same value as in the Authentication Request.
 * - `codeVerifier`: only when PKCE is used. It is sent to the Token Endpoint as `code_verifier`, and the
 *   `code_challenge` of the Authentication Request is derived from it. Keep its string value in the session, and
 *   create it again with `new PkceCodeVerifier($value)`.
 *
 * `state` and `nonce` are always present, and always validated. For the flows which do not send them, use
 * {@see UnsafeAuthenticationRequestContext}.
 *
 * Use {@see \OpenIDConnect\Client::createAuthenticationRequestContext()} to get random `state` and `nonce`. Keep
 * the values in the user's session, and remove them once the callback is handled, so a `state` cannot be used twice.
 *
 * @link https://openid.net/specs/openid-connect-core-1_0.html#AuthRequest
 * @link https://openid.net/specs/openid-connect-core-1_0.html#IDTokenValidation
 * @link https://www.rfc-editor.org/rfc/rfc6749.html#section-4.1.1
 * @link https://www.rfc-editor.org/rfc/rfc6749.html#section-4.1.3
 * @link https://www.rfc-editor.org/rfc/rfc6749.html#section-10.12
 * @link https://www.rfc-editor.org/rfc/rfc7636.html#section-4.5
 */
final class AuthenticationRequestContext implements AuthenticationRequestContextInterface
{
    public function __construct(
        public readonly string $redirectUri,
        public readonly string $state,
        public readonly string $nonce,
        public readonly ?PkceCodeVerifier $codeVerifier = null
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function redirectUri(): string
    {
        return $this->redirectUri;
    }

    /**
     * {@inheritDoc}
     */
    public function state(): string
    {
        return $this->state;
    }

    /**
     * {@inheritDoc}
     */
    public function nonce(): string
    {
        return $this->nonce;
    }

    /**
     * {@inheritDoc}
     */
    public function codeVerifier(): ?PkceCodeVerifier
    {
        return $this->codeVerifier;
    }
}
