<?php

declare(strict_types=1);

namespace OpenIDConnect;

use InvalidArgumentException;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Stringable;

/**
 * The `code_verifier` of PKCE, and the `code_challenge` which is derived from it.
 *
 * The `code_verifier` is sent to the Token Endpoint. Only its `code_challenge` is sent in the Authentication Request.
 * To keep it in the user's session, store the string value (`(string)$verifier` or {@see self::value()}), and create it
 * again with `new PkceCodeVerifier($value)` in the callback.
 *
 * @link https://www.rfc-editor.org/rfc/rfc7636.html#section-4.1
 * @link https://www.rfc-editor.org/rfc/rfc7636.html#section-4.2
 */
final class PkceCodeVerifier implements Stringable
{
    private readonly string $value;

    /**
     * @param string $value MUST follow RFC 7636 section 4.1
     * @throws InvalidArgumentException When the value is not a valid `code_verifier`
     */
    public function __construct(string $value)
    {
        // code-verifier = 43*128unreserved
        // unreserved = ALPHA / DIGIT / "-" / "." / "_" / "~"
        if (1 !== preg_match('/^[A-Za-z0-9\-._~]{43,128}$/D', $value)) {
            throw new InvalidArgumentException(
                'The code_verifier must be 43 to 128 characters of [A-Z] / [a-z] / [0-9] / "-" / "." / "_" / "~"'
            );
        }

        $this->value = $value;
    }

    /**
     * Create a random `code_verifier`.
     */
    public static function generate(): self
    {
        // It is RECOMMENDED that the output of a suitable random number generator be used to create a
        // 32-octet sequence. The octet sequence is then base64url-encoded to produce a 43-octet URL safe string.
        return new self(Base64UrlSafe::encodeUnpadded(random_bytes(32)));
    }

    /**
     * The `code_challenge` of the "S256" method.
     */
    public function challenge(): string
    {
        // code_challenge = BASE64URL-ENCODE(SHA256(ASCII(code_verifier)))
        return Base64UrlSafe::encodeUnpadded(hash('sha256', $this->value, true));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
