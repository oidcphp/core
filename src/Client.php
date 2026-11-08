<?php

declare(strict_types=1);

namespace OpenIDConnect;

use InvalidArgumentException;
use MilesChou\Psr\Http\Client\HttpClientAwareTrait;
use MilesChou\Psr\Http\Client\HttpClientInterface;
use MilesChou\Psr\Http\Message\HttpFactoryAwareTrait;
use OpenIDConnect\Contracts\AuthenticationRequestContextInterface;
use OpenIDConnect\Exceptions\OAuth2ClientException;
use OpenIDConnect\Exceptions\OAuth2ServerException;
use OpenIDConnect\Http\Authentication\ClientAuthenticationAwareTrait;
use OpenIDConnect\Http\Query;
use OpenIDConnect\Http\Response\AuthorizationFormPostResponseBuilder;
use OpenIDConnect\Http\Response\AuthorizationRedirectResponseBuilder;
use OpenIDConnect\Http\Response\InitiateLogoutFormPostResponseBuilder;
use OpenIDConnect\Http\Response\InitiateLogoutRedirectResponseBuilder;
use OpenIDConnect\Jwt\Verifiers\IdTokenVerifier;
use OpenIDConnect\Traits\ClockTolerance;
use OpenIDConnect\Traits\ConfigAwareTrait;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * OAuth 2.0 / OpenID Connect Client
 */
class Client
{
    use Concerns\RevokeAction;
    use Concerns\TokenAction;
    use ClientAuthenticationAwareTrait;
    use ClockTolerance;
    use ConfigAwareTrait;
    use HttpClientAwareTrait;
    use HttpFactoryAwareTrait;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * The values of the latest Authentication Request. `getState()` and `getNonce()` read from it.
     */
    private ?AuthenticationRequestContext $authenticationRequestContext = null;

    /**
     * @param Config $config
     * @param HttpClientInterface $httpClient
     */
    public function __construct(Config $config, HttpClientInterface $httpClient)
    {
        $this->setConfig($config);
        $this->setHttpClient($httpClient);
    }

    /**
     * Create PSR-7 response with form post
     *
     * @param array<string, mixed> $parameters
     * @param AuthenticationRequestContextInterface|null $context Given `state`, `nonce` and `redirect_uri` are used
     * @return ResponseInterface
     */
    public function createAuthorizeFormPostResponse(
        array $parameters = [],
        ?AuthenticationRequestContextInterface $context = null
    ): ResponseInterface {
        return (new AuthorizationFormPostResponseBuilder($this->config, $this->httpClient))
            ->build($this->generateAuthorizationParameters($this->applyContext($parameters, $context)));
    }

    /**
     * Create PSR-7 redirect response
     *
     * @param array<string, mixed> $parameters
     * @param AuthenticationRequestContextInterface|null $context Given `state`, `nonce` and `redirect_uri` are used
     * @return ResponseInterface
     */
    public function createAuthorizeRedirectResponse(
        array $parameters = [],
        ?AuthenticationRequestContextInterface $context = null
    ): ResponseInterface {
        return (new AuthorizationRedirectResponseBuilder($this->config, $this->httpClient))
            ->build($this->generateAuthorizationParameters($this->applyContext($parameters, $context)));
    }

    /**
     * Create PSR-7 redirect response
     *
     * @param array<string, mixed> $parameters
     * @return ResponseInterface
     */
    public function initiateLogoutFormPostResponse(array $parameters = []): ResponseInterface
    {
        return (new InitiateLogoutFormPostResponseBuilder($this->config, $this->httpClient))
            ->build($parameters);
    }

    /**
     * Create PSR-7 redirect response
     *
     * @param array<string, mixed> $parameters
     * @return ResponseInterface
     */
    public function initiateLogoutRedirectResponse(array $parameters = []): ResponseInterface
    {
        return (new InitiateLogoutRedirectResponseBuilder($this->config, $this->httpClient))
            ->build($parameters);
    }

    /**
     * @return null|string
     */
    public function getNonce(): ?string
    {
        return $this->authenticationRequestContext?->nonce;
    }

    /**
     * @return null|string
     */
    public function getState(): ?string
    {
        return $this->authenticationRequestContext?->state;
    }

    /**
     * Create a context for a new Authentication Request, with random `state` and `nonce`.
     *
     * PKCE is used by default, so the context has a random `codeVerifier`, and `createAuthorize*Response()` adds the
     * `code_challenge` of it. To handle PKCE by yourself, give your own `$codeVerifier`, or turn `$pkce` off and
     * give `code_challenge` and `code_challenge_method` as parameters of `createAuthorize*Response()`.
     *
     * @param string $redirectUri
     * @param bool $pkce Create a random `codeVerifier` when `$codeVerifier` is not given
     * @param PkceCodeVerifier|null $codeVerifier Used as it is, instead of a random one
     * @return AuthenticationRequestContext
     */
    public function createAuthenticationRequestContext(
        string $redirectUri,
        bool $pkce = true,
        ?PkceCodeVerifier $codeVerifier = null
    ): AuthenticationRequestContext {
        $codeVerifier ??= $pkce ? PkceCodeVerifier::generate() : null;

        return new AuthenticationRequestContext(
            $redirectUri,
            $this->generateRandomString(),
            $this->generateRandomString(),
            $codeVerifier
        );
    }

    /**
     * Handle the Authentication Response, and exchange the code for tokens at the Token Endpoint.
     *
     * The context MUST be the one which was created before redirecting the user to the OpenID Provider.
     *
     * @param UriInterface $callbackUri The full URI which the OpenID Provider redirected the user to
     * @param AuthenticationRequestContextInterface $context
     * @return TokenSet
     * @link https://openid.net/specs/openid-connect-core-1_0.html#AuthResponse
     * @link https://openid.net/specs/openid-connect-core-1_0.html#IDTokenValidation
     * @link https://www.rfc-editor.org/rfc/rfc6749.html#section-4.1.2
     * @link https://www.rfc-editor.org/rfc/rfc6749.html#section-4.1.3
     * @link https://www.rfc-editor.org/rfc/rfc7636.html#section-4.5
     */
    public function handleCallback(
        UriInterface $callbackUri,
        AuthenticationRequestContextInterface $context
    ): TokenSet
    {
        $parameters = Query::parse($callbackUri->getQuery());

        // The `state` was sent in the Authentication Request, so the response MUST give it back unchanged.
        // It is compared in constant time. A context without `state` skips the check.
        $expectedState = $context->state();

        if (null !== $expectedState) {
            if (!isset($parameters['state']) || !is_string($parameters['state'])) {
                throw new OAuth2ClientException("'state' missing from the response");
            }

            if (!hash_equals($expectedState, $parameters['state'])) {
                throw new OAuth2ClientException('State mismatch');
            }
        }

        // An error response is only trusted after its `state` is validated.
        if (isset($parameters['error'])) {
            $error = is_string($parameters['error']) ? $parameters['error'] : 'invalid_response';
            $description = isset($parameters['error_description']) && is_string($parameters['error_description'])
                ? ': ' . $parameters['error_description']
                : '';

            throw new OAuth2ServerException($error . $description);
        }

        if (!isset($parameters['code']) || !is_string($parameters['code'])) {
            throw new InvalidArgumentException("'code' missing from the response");
        }

        // Only the parameters of the Token Request are sent. The `state` and `nonce` are for validation only.
        // The `code_verifier` is a parameter of the Token Request when PKCE is used.
        $parsed = $this->token(array_filter([
            'code' => $parameters['code'],
            'redirect_uri' => $context->redirectUri(),
            'code_verifier' => $context->codeVerifier()?->value(),
        ], static fn ($value) => null !== $value));

        $tokenSet = new TokenSet($this->config, $parsed, $this->clockTolerance());

        // Verify ID Token when exist
        if ($tokenSet->idToken()) {
            $idTokenVerifier = new IdTokenVerifier($this->config, $this->clockTolerance());

            // 11. If a nonce value was sent in the Authentication Request, a nonce Claim MUST be present and its
            //     value checked to verify that it is the same value as the one that was sent in the
            //     Authentication Request.
            //
            // The default context always has a nonce, so a nonce Claim is required. A context without `nonce`
            // skips the check.
            $expectedNonce = $context->nonce();

            $idTokenVerifier->verify(
                $tokenSet->idToken(),
                null !== $expectedNonce ? ['nonce'] : [],
                null !== $expectedNonce ? ['nonce' => $expectedNonce] : []
            );
        }

        return $tokenSet;
    }

    /**
     * Initial the context of the Authentication Request, which `getState()` and `getNonce()` read from.
     *
     * The `state` and `nonce` are random unless they are given, or already initialized.
     *
     * @param array<mixed> $options
     */
    public function initAuthenticationRequestContext(array $options = []): AuthenticationRequestContext
    {
        $current = $this->authenticationRequestContext;

        return $this->authenticationRequestContext = new AuthenticationRequestContext(
            $options['redirect_uri'] ?? $current->redirectUri ?? '',
            !empty($options['state']) ? $options['state'] : $current->state ?? $this->generateRandomString(),
            !empty($options['nonce']) ? $options['nonce'] : $current->nonce ?? $this->generateRandomString()
        );
    }

    /**
     * Initial the authorization parameters
     *
     * @param array<mixed> $options
     * @deprecated Use {@see Client::initAuthenticationRequestContext()} instead.
     */
    public function initAuthorizationParameters(array $options = []): void
    {
        $this->initAuthenticationRequestContext($options);
    }

    /**
     * Generate the random string
     *
     * @param int<2, max> $length
     * @return string
     */
    protected function generateRandomString(int $length = 32): string
    {
        return bin2hex(random_bytes($length >> 1));
    }

    /**
     * @param array<mixed> $parameters
     *
     * @return array<mixed>
     */
    private function generateAuthorizationParameters(array $parameters): array
    {
        $parameters['state'] = $this->initAuthenticationRequestContext($parameters)->state;

        if (empty($parameters['scope'])) {
            $parameters['scope'] = ['openid'];
        }

        $parameters += [
            'response_type' => 'code',
        ];

        if (is_array($parameters['scope'])) {
            $parameters['scope'] = implode(' ', $parameters['scope']);
        }

        if (!isset($parameters['redirect_uri'])) {
            throw new OAuth2ClientException("Missing parameter 'redirect_uri'");
        }

        $parameters['client_id'] = $this->config->requireClientMetadata('client_id');

        return $parameters;
    }

    /**
     * The values in the context win, so the Authentication Request and the later callback use the same values.
     *
     * @param array<mixed> $parameters
     * @param AuthenticationRequestContextInterface|null $context
     * @return array<mixed>
     */
    private function applyContext(array $parameters, ?AuthenticationRequestContextInterface $context): array
    {
        if (null === $context) {
            return $parameters;
        }

        $parameters = array_merge($parameters, array_filter([
            'redirect_uri' => $context->redirectUri(),
            'state' => $context->state(),
            'nonce' => $context->nonce(),
        ], static fn ($value) => null !== $value));

        // The `code_verifier` is never sent in the Authentication Request. A `code_challenge` given by the caller is
        // kept, so the caller is able to handle PKCE by itself.
        $codeVerifier = $context->codeVerifier();

        if (null !== $codeVerifier && !isset($parameters['code_challenge'])) {
            // If the client is capable of using "S256", it MUST use "S256", as "S256" is Mandatory To Implement (MTI)
            // on the server.
            //
            // The `code_challenge_method` defaults to "plain" if it is not present, so "S256" is always given.
            $parameters['code_challenge'] = $codeVerifier->challenge();
            $parameters['code_challenge_method'] = 'S256';
        }

        return $parameters;
    }

    /**
     * Parse response
     *
     * @param ResponseInterface $response
     * @return array<string, mixed>
     */
    private function parseResponse(ResponseInterface $response): array
    {
        $content = (string)$response->getBody();

        // Just using JSON decode, See https://www.rfc-editor.org/rfc/rfc6749.html#section-5.1
        $parsed = json_decode($content, true);

        if (!is_array($parsed)) {
            throw new OAuth2ServerException('Invalid response received from token endpoint. Expected JSON.');
        }

        if (isset($parsed['error'])) {
            throw new OAuth2ServerException($parsed['error']);
        }

        return $parsed;
    }
}
