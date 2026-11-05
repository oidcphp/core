<?php

declare(strict_types=1);

namespace OpenIDConnect;

use InvalidArgumentException;

/**
 * The values the Relying Party binds to one Authentication Request, and needs again when the response comes back.
 *
 * It is NOT the Authentication Request itself, so it does not hold request parameters such as `scope` or `prompt`.
 * Only the values which must be kept until the Authentication Response is handled are allowed:
 *
 * - `state`: used to validate the response, it is not a parameter of the Token Request.
 * - `nonce`: used to validate the ID Token, it is not a parameter of the Token Request.
 * - `redirect_uri`: it MUST be sent to the Token Endpoint with the same value as in the Authentication Request.
 *
 * The object is created before redirecting the user to the OpenID Provider, and the caller should keep it in
 * the user's session using {@see AuthenticationRequestContext::toArray()}. When the user comes back, restore it
 * by {@see AuthenticationRequestContext::fromArray()} and give it to {@see \OpenIDConnect\Client::handleCallback()}.
 * It should be removed from the session once the callback is handled, so a `state` cannot be used twice.
 *
 * @link https://openid.net/specs/openid-connect-core-1_0.html#AuthRequest
 * @link https://openid.net/specs/openid-connect-core-1_0.html#IDTokenValidation
 * @link https://www.rfc-editor.org/rfc/rfc6749.html#section-4.1.1
 * @link https://www.rfc-editor.org/rfc/rfc6749.html#section-4.1.3
 * @link https://www.rfc-editor.org/rfc/rfc6749.html#section-10.12
 */
final class AuthenticationRequestContext
{
    private const KEYS = ['redirect_uri', 'state', 'nonce'];

    /**
     * @var string
     */
    private $redirectUri;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $nonce;

    private function __construct(string $redirectUri, string $state, string $nonce)
    {
        foreach (['redirect_uri' => $redirectUri, 'state' => $state, 'nonce' => $nonce] as $name => $value) {
            if ('' === $value) {
                throw new InvalidArgumentException("'{$name}' must not be empty");
            }
        }

        $this->redirectUri = $redirectUri;
        $this->state = $state;
        $this->nonce = $nonce;
    }

    /**
     * Create a context for a new Authentication Request.
     *
     * The `state` and `nonce` are random strings from a CSPRNG unless they are given.
     *
     * @param string $redirectUri
     * @param string|null $state
     * @param string|null $nonce
     * @return self
     */
    public static function create(string $redirectUri, ?string $state = null, ?string $nonce = null): self
    {
        return new self($redirectUri, $state ?? self::randomString(), $nonce ?? self::randomString());
    }

    /**
     * Restore the context which was stored by {@see AuthenticationRequestContext::toArray()}.
     *
     * Only the exact keys of {@see AuthenticationRequestContext::toArray()} are accepted, and each value MUST be a
     * non-empty string. The data from a session is not trusted to have the right shape.
     *
     * @param array<mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $unknown = array_diff(array_keys($data), self::KEYS);

        if ([] !== $unknown) {
            throw new InvalidArgumentException('Unknown key: ' . implode(', ', array_map('strval', $unknown)));
        }

        foreach (self::KEYS as $key) {
            if (!isset($data[$key]) || !is_string($data[$key])) {
                throw new InvalidArgumentException("'{$key}' must be a string");
            }
        }

        return new self($data['redirect_uri'], $data['state'], $data['nonce']);
    }

    public function redirectUri(): string
    {
        return $this->redirectUri;
    }

    public function state(): string
    {
        return $this->state;
    }

    public function nonce(): string
    {
        return $this->nonce;
    }

    /**
     * The array is safe to be stored in the session, and only contains strings.
     *
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

    private static function randomString(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }
}
