<?php

declare(strict_types=1);

namespace OpenIDConnect\Traits;

use InvalidArgumentException;
use ParagonIE\ConstantTime\Base64UrlSafe;

/**
 * The shared part of the `code_verifier`: the validation and the random generation.
 *
 * @internal
 * @link https://www.rfc-editor.org/rfc/rfc7636.html#section-4.1
 */
trait PkceCodeVerifierTrait
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
    public static function generate(): static
    {
        // It is RECOMMENDED that the output of a suitable random number generator be used to create a
        // 32-octet sequence. The octet sequence is then base64url-encoded to produce a 43-octet URL safe string.
        return new static(Base64UrlSafe::encodeUnpadded(random_bytes(32)));
    }

    /**
     * {@inheritDoc}
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
