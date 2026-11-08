<?php

declare(strict_types=1);

namespace OpenIDConnect;

use OpenIDConnect\Contracts\AuthenticationRequestContextInterface;
use OpenIDConnect\Contracts\PkceCodeVerifierInterface;

/**
 * An {@see AuthenticationRequestContext} whose `state` and `nonce` are optional.
 *
 * Both are only RECOMMENDED or OPTIONAL by the specifications for the Authorization Code Flow, but a missing value
 * means the check is skipped:
 *
 * - without `state`, the Authentication Response is not protected against CSRF, and an error response is trusted
 *   without it.
 * - without `nonce`, the ID Token is not bound to the Authentication Request, and it is not replay protected.
 *
 * Use {@see AuthenticationRequestContext} unless you have to skip them.
 *
 * @link https://www.rfc-editor.org/rfc/rfc6749.html#section-10.12
 * @link https://openid.net/specs/openid-connect-core-1_0.html#NonceNotes
 */
final class UnsafeAuthenticationRequestContext implements AuthenticationRequestContextInterface
{
    public function __construct(
        public readonly string $redirectUri,
        public readonly ?string $state = null,
        public readonly ?string $nonce = null,
        public readonly ?PkceCodeVerifierInterface $codeVerifier = null
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
    public function state(): ?string
    {
        return $this->state;
    }

    /**
     * {@inheritDoc}
     */
    public function nonce(): ?string
    {
        return $this->nonce;
    }

    /**
     * {@inheritDoc}
     */
    public function codeVerifier(): ?PkceCodeVerifierInterface
    {
        return $this->codeVerifier;
    }
}
