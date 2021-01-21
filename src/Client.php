<?php

declare(strict_types=1);

namespace OpenIDConnect;

use InvalidArgumentException;
use MilesChou\Psr\Http\Client\HttpClientAwareTrait;
use MilesChou\Psr\Http\Client\HttpClientInterface;
use MilesChou\Psr\Http\Message\HttpFactoryAwareTrait;
use OpenIDConnect\Contracts\TokenFactoryInterface;
use OpenIDConnect\Contracts\TokenSetInterface;
use OpenIDConnect\Exceptions\OAuth2ClientException;
use OpenIDConnect\Exceptions\OAuth2ServerException;
use OpenIDConnect\Http\Authentication\ClientAuthenticationAwareTrait;
use OpenIDConnect\Http\Request\TokenRequestBuilder;
use OpenIDConnect\Http\Response\AuthorizationFormResponseBuilder;
use OpenIDConnect\Http\Response\AuthorizationRedirectResponseBuilder;
use OpenIDConnect\OAuth2\Grant\AuthorizationCode;
use OpenIDConnect\OAuth2\Grant\GrantType;
use OpenIDConnect\Token\TokenFactory;
use OpenIDConnect\Traits\ClockTolerance;
use OpenIDConnect\Traits\ConfigAwareTrait;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * OAuth 2.0 / OpenID Connect Client
 */
class Client
{
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
     * @var null|string
     */
    private $nonce;

    /**
     * @var null|string
     */
    private $state;

    /**
     * @var TokenFactoryInterface
     */
    private $tokenFactory;

    /**
     * @param Config $config
     * @param HttpClientInterface $httpClient
     * @param TokenFactoryInterface|null $tokenFactory
     */
    public function __construct(
        Config $config,
        HttpClientInterface $httpClient,
        TokenFactoryInterface $tokenFactory = null
    ) {
        $this->setConfig($config);
        $this->setHttpClient($httpClient);

        $this->tokenFactory = $tokenFactory ?? new TokenFactory($config);
    }

    /**
     * Create PSR-7 response with form post
     *
     * @param array $parameters
     * @return ResponseInterface
     */
    public function createAuthorizeFormPostResponse(array $parameters = []): ResponseInterface
    {
        return (new AuthorizationFormResponseBuilder($this->config, $this->httpClient))
            ->build($this->generateAuthorizationParameters($parameters));
    }

    /**
     * Create PSR-7 redirect response
     *
     * @param array $parameters
     * @return ResponseInterface
     */
    public function createAuthorizeRedirectResponse(array $parameters = []): ResponseInterface
    {
        return (new AuthorizationRedirectResponseBuilder($this->config, $this->httpClient))
            ->build($this->generateAuthorizationParameters($parameters));
    }

    /**
     * @return null|string
     */
    public function getNonce(): ?string
    {
        return $this->nonce;
    }

    /**
     * @return null|string
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param array $parameters
     * @param array $checks
     * @return TokenSetInterface
     */
    public function handleCallback(array $parameters, array $checks = []): TokenSetInterface
    {
        if (!isset($parameters['code'])) {
            throw new InvalidArgumentException("'code' missing from the response");
        }

        if (isset($parameters['state']) && !isset($checks['state'])) {
            throw new InvalidArgumentException("'state' argument is missing");
        }

        if (!isset($parameters['state']) && isset($checks['state'])) {
            throw new OAuth2ClientException("'state' missing from the response");
        }

        if (isset($parameters['state'], $checks['state']) && ($checks['state'] !== $parameters['state'])) {
            $msg = "State mismatch, expected {$checks['state']}, got: {$parameters['state']}";
            throw new OAuth2ClientException($msg);
        }

        return $this->sendTokenRequest($parameters, $checks);
    }

    /**
     * Initial the authorization parameters
     *
     * @param array<mixed> $options
     */
    public function initAuthorizationParameters(array $options = []): void
    {
        if (!empty($options['state'])) {
            $this->state = $options['state'];
        }

        if (null === $this->state) {
            $this->state = $this->generateRandomString();
        }

        if (!empty($options['nonce'])) {
            $this->nonce = $options['nonce'];
        }

        if (null === $this->nonce) {
            $this->nonce = $this->generateRandomString();
        }
    }

    /**
     * @param array<mixed> $parameters
     * @param array<mixed> $checks
     * @param GrantType|null $grant Default is AuthorizationCode.
     * @return TokenSetInterface
     */
    public function sendTokenRequest(
        array $parameters = [],
        array $checks = [],
        GrantType $grant = null
    ): TokenSetInterface {
        $grant = $grant ?? new AuthorizationCode();

        $request = (new TokenRequestBuilder($this->config, $this->httpClient))
            ->setClientAuthentication($this->clientAuthentication)
            ->build(array_merge($parameters, $checks), $grant);

        try {
            $response = $request->send();
        } catch (ClientExceptionInterface $e) {
            $msg = 'Token endpoint return error: ' . $e->getMessage();
            throw new OAuth2ServerException($msg, 0, $e);
        }

        $parsed = $this->parseTokenResponse($response);

        return $this->tokenFactory->create(array_merge($checks, $parsed), $this->clockTolerance());
    }

    /**
     * Generate the random string
     *
     * @param int $length
     * @return string
     */
    protected function generateRandomString(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * @param array<mixed> $parameters
     *
     * @return array<mixed>
     */
    private function generateAuthorizationParameters(array $parameters): array
    {
        $this->initAuthorizationParameters($parameters);

        $parameters['state'] = $this->state;

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
     * Parse response from token endpoint
     *
     * @param ResponseInterface $response
     * @return array
     */
    private function parseTokenResponse(ResponseInterface $response): array
    {
        $content = (string)$response->getBody();

        // Just using JSON decode, See https://tools.ietf.org/html/rfc6749#section-5.1
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
