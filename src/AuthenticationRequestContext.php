<?php

declare(strict_types=1);

namespace OpenIDConnect;

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
 *
 * Use {@see \OpenIDConnect\Client::createAuthenticationRequestContext()} to get random `state` and `nonce`. Keep
 * the object in the user's session using {@see AuthenticationRequestContext::toArray()}, restore it by
 * {@see AuthenticationRequestContext::fromArray()} when the user comes back, and remove it from the session once
 * the callback is handled, so a `state` cannot be used twice.
 *
 * @link https://openid.net/specs/openid-connect-core-1_0.html#AuthRequest
 * @link https://openid.net/specs/openid-connect-core-1_0.html#IDTokenValidation
 * @link https://www.rfc-editor.org/rfc/rfc6749.html#section-4.1.1
 * @link https://www.rfc-editor.org/rfc/rfc6749.html#section-4.1.3
 * @link https://www.rfc-editor.org/rfc/rfc6749.html#section-10.12
 */
final class AuthenticationRequestContext
{
    public function __construct(
        public readonly string $redirectUri,
        public readonly string $state,
        public readonly string $nonce
    ) {
    }

    /**
     * Map the array, which was made by {@see AuthenticationRequestContext::toArray()}, to the properties.
     *
     * A missing key or a value which is not a string is a TypeError, because of the property types.
     *
     * @param array<mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self($data['redirect_uri'] ?? null, $data['state'] ?? null, $data['nonce'] ?? null);
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'redirect_uri' => $this->redirectUri,
            'state' => $this->state,
            'nonce' => $this->nonce,
        ];
    }
}
