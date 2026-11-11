<?php

declare(strict_types=1);

namespace OpenIDConnect\Contracts;

use Stringable;

/**
 * The `code_verifier` of PKCE, and the `code_challenge` with the `code_challenge_method` which are derived from it.
 *
 * The `code_verifier` is sent to the Token Endpoint. The `code_challenge` and the `code_challenge_method` are sent in
 * the Authentication Request.
 *
 * @link https://www.rfc-editor.org/rfc/rfc7636.html#section-4
 */
interface PkceCodeVerifierInterface extends Stringable
{
    /**
     * The `code_verifier`.
     */
    public function value(): string;

    /**
     * The `code_challenge` which is derived from the `code_verifier` by the method.
     */
    public function challenge(): string;

    /**
     * The `code_challenge_method`, e.g. "S256" or "plain".
     */
    public function challengeMethod(): string;
}
