<?php

declare(strict_types=1);

namespace OpenIDConnect;

use OpenIDConnect\Contracts\PkceCodeVerifierInterface;
use OpenIDConnect\Traits\PkceCodeVerifierTrait;

/**
 * The `code_verifier` with the "plain" method.
 *
 * The `code_challenge` is the `code_verifier` itself, so the `code_verifier` is sent in the Authentication Request.
 * It is only for the clients which cannot use "S256", use {@see S256PkceCodeVerifier} otherwise.
 *
 * @link https://www.rfc-editor.org/rfc/rfc7636.html#section-4.2
 */
final class PlainPkceCodeVerifier implements PkceCodeVerifierInterface
{
    use PkceCodeVerifierTrait;

    public const METHOD = 'plain';

    /**
     * {@inheritDoc}
     */
    public function challenge(): string
    {
        // code_challenge = code_verifier
        return $this->value();
    }

    /**
     * {@inheritDoc}
     */
    public function challengeMethod(): string
    {
        return self::METHOD;
    }
}
