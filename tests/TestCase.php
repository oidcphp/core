<?php

namespace Tests;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response as HttpResponse;
use Http\Adapter\Guzzle6\Client as Psr18Client;
use Illuminate\Container\Container;
use Jose\Component\Core\JWKSet;
use Jose\Component\Core\Util\JsonConverter;
use Jose\Component\KeyManagement\JWKFactory;
use OpenIDConnect\Core\Builder;
use OpenIDConnect\Core\Token\TokenFactory;
use OpenIDConnect\OAuth2\Metadata\ClientInformation;
use OpenIDConnect\OAuth2\Metadata\ProviderMetadata;
use OpenIDConnect\OAuth2\Token\TokenFactoryInterface;
use OpenIDConnect\Support\Laravel\HttpFactoryServiceProvider;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

use function GuzzleHttp\json_encode;

class TestCase extends BaseTestCase
{
    protected function createClientInformation($overwrite = []): ClientInformation
    {
        return new ClientInformation($this->createClientInformationConfig($overwrite));
    }

    protected function createClientInformationConfig($overwrite = []): array
    {
        return array_merge([
            'client_id' => 'some_id',
            'client_secret' => 'some_secret',
            'redirect_uri' => 'https://someredirect',
            'redirect_uris' => ['https://someredirect'],
        ], $overwrite);
    }

    protected function createContainer(array $instances = []): ContainerInterface
    {
        $container = new Container();

        $container->singleton(ClientInterface::class, function () use ($instances) {
            if (empty($instances[ClientInterface::class])) {
                return $this->createHttpClient();
            }

            return $instances[ClientInterface::class];
        });

        $container->singleton(TokenFactoryInterface::class, function () use ($instances) {
            if (empty($instances[TokenFactoryInterface::class])) {
                return new TokenFactory();
            }

            return $instances[TokenFactoryInterface::class];
        });

        (new HttpFactoryServiceProvider($container))->register();

        if (isset($instances[StreamFactoryInterface::class])) {
            $container->instance(StreamFactoryInterface::class, $instances[StreamFactoryInterface::class]);
        }

        if (isset($instances[ResponseFactoryInterface::class])) {
            $container->instance(ResponseFactoryInterface::class, $instances[ResponseFactoryInterface::class]);
        }

        if (isset($instances[RequestFactoryInterface::class])) {
            $container->instance(RequestFactoryInterface::class, $instances[RequestFactoryInterface::class]);
        }

        if (isset($instances[UriFactoryInterface::class])) {
            $container->instance(UriFactoryInterface::class, $instances[UriFactoryInterface::class]);
        }

        return $container;
    }

    /**
     * @param array $provider
     * @param array $client
     * @return Builder
     */
    protected function createFactory($provider = [], $client = []): Builder
    {
        return new Builder(
            $this->createProviderMetadata($provider),
            $this->createClientInformation($client)
        );
    }

    /**
     * Creates HTTP client.
     *
     * @param ResponseInterface|ResponseInterface[] $responses
     * @param array $history
     * @return HandlerStack
     */
    protected function createHandlerStack($responses = [], &$history = []): HandlerStack
    {
        if (!is_array($responses)) {
            $responses = [$responses];
        }

        $handler = HandlerStack::create(new MockHandler($responses));
        $handler->push(Middleware::history($history));

        return $handler;
    }

    /**
     * Creates HTTP client.
     *
     * @param ResponseInterface|ResponseInterface[] $responses
     * @param array $history
     * @return ClientInterface
     */
    protected function createHttpClient($responses = [], &$history = []): ClientInterface
    {
        return new Psr18Client(new HttpClient($this->createHttpMockOption($responses, $history)));
    }

    /**
     * @param array $data
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    protected function createHttpJsonResponse(
        array $data = [],
        int $status = 200,
        array $headers = []
    ): ResponseInterface {
        return new HttpResponse($status, $headers, json_encode($data));
    }

    /**
     * @param ResponseInterface|ResponseInterface[] $responses
     * @param array $history
     * @return array
     */
    protected function createHttpMockOption($responses = [], &$history = []): array
    {
        return [
            'handler' => $this->createHandlerStack($responses, $history),
        ];
    }

    protected function createJwkSet($jwks = []): JWKSet
    {
        if (empty($jwks)) {
            $jwks = [JWKFactory::createRSAKey(1024, ['alg' => 'RS256'])];
        }

        return new JWKSet($jwks);
    }

    protected function createProviderMetadata($overwrite = [], $jwks = null): ProviderMetadata
    {
        return new ProviderMetadata(
            $this->createProviderMetadataConfig($overwrite),
            new \OpenIDConnect\OAuth2\Metadata\JwkSet(JsonConverter::decode(JsonConverter::encode($this->createJwkSet($jwks))))
        );
    }

    /**
     * @param array $overwrite
     * @return array
     */
    protected function createProviderMetadataConfig($overwrite = []): array
    {
        return array_merge([
            'issuer' => 'https://somewhere',
            'authorization_endpoint' => 'https://somewhere/auth',
            'token_endpoint' => 'https://somewhere/token',
            'jwks_uri' => 'https://somewhere/certs',
            'response_types_supported' => ['code'],
            'subject_types_supported' => ['public'],
            'id_token_signing_alg_values_supported' => ['RS256'],
        ], $overwrite);
    }

    /**
     * @param array $overwrite
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    protected function createFakeTokenEndpointResponse($overwrite = [], $status = 200, $headers = []): ResponseInterface
    {
        return $this->createHttpJsonResponse($this->createFakeTokenSetParameter($overwrite), $status, $headers);
    }

    /**
     * @param array $overwrite
     * @return array
     */
    protected function createFakeTokenSetParameter($overwrite = []): array
    {
        return array_merge([
            'access_token' => 'some-access-token',
            'expires_in' => 3600,
            'id_token' => null,
            'refresh_token' => 'some-refresh-token',
            'scope' => 'some-scope',
        ], $overwrite);
    }
}
