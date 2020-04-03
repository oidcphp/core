<?php

namespace Tests;

use Illuminate\Container\Container;
use Jose\Component\Core\JWKSet;
use Jose\Component\Core\Util\JsonConverter;
use Jose\Component\KeyManagement\JWKFactory;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UploadedFileFactory;
use Laminas\Diactoros\UriFactory;
use MilesChou\Mocker\Psr18\MockClient;
use MilesChou\Psr\Http\Message\HttpFactory;
use MilesChou\Psr\Http\Message\HttpFactoryInterface;
use OpenIDConnect\Config\ClientInformation;
use OpenIDConnect\Config\ProviderMetadata;
use OpenIDConnect\Core\Builder;
use OpenIDConnect\Core\Token\TokenFactory;
use OpenIDConnect\OAuth2\Token\TokenFactoryInterface;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

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
            'redirect_uris' => ['https://someredirect'],
        ], $overwrite);
    }

    protected function createContainer(array $instances = []): ContainerInterface
    {
        $container = new Container();

        $container->singleton(ClientInterface::class, function () use ($instances) {
            if (empty($instances[ClientInterface::class])) {
                return new MockClient();
            }

            return $instances[ClientInterface::class];
        });

        $container->singleton(TokenFactoryInterface::class, function () use ($instances) {
            if (empty($instances[TokenFactoryInterface::class])) {
                return new TokenFactory();
            }

            return $instances[TokenFactoryInterface::class];
        });

        $container->singleton(HttpFactoryInterface::class, HttpFactory::class);

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
            new \OpenIDConnect\Config\JwkSet(JsonConverter::decode(JsonConverter::encode($this->createJwkSet($jwks))))
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
