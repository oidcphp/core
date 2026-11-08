<?php

declare(strict_types=1);

namespace OpenIDConnect;

use ParagonIE\ConstantTime\Base64UrlSafe;

/**
 * The `code_verifier` with the "S256" method.
 *
 * If the client is capable of using "S256", it MUST use "S256", as "S256" is Mandatory To Implement (MTI) on the
 * server. Only the hash of the `code_verifier` is sent in the Authentication Request.
 *
 * @link https://www.rfc-editor.org/rfc/rfc7636.html#section-4.2
 */
final class S256PkceCodeVerifier extends AbstractPkceCodeVerifier
{
    public const METHOD = 'S256';

    /**
     * {@inheritDoc}
     */
    public function challenge(): string
    {
        // code_challenge = BASE64URL-ENCODE(SHA256(ASCII(code_verifier)))
        return Base64UrlSafe::encodeUnpadded(hash('sha256', $this->value(), true));
    }

    /**
     * {@inheritDoc}
     */
    public function challengeMethod(): string
    {
        return self::METHOD;
    }
}
