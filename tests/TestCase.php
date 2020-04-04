<?php

namespace Tests;

use Illuminate\Container\Container;
use Jose\Component\Core\JWKSet;
use Jose\Component\Core\Util\JsonConverter;
use Jose\Component\KeyManagement\JWKFactory;
use MilesChou\Mocker\Psr18\MockClient;
use MilesChou\Psr\Http\Message\HttpFactory;
use MilesChou\Psr\Http\Message\HttpFactoryInterface;
use OpenIDConnect\Config;
use OpenIDConnect\Contracts\TokenFactoryInterface;
use OpenIDConnect\Metadata\ClientMetadata;
use OpenIDConnect\Metadata\ProviderMetadata;
use OpenIDConnect\Token\TokenFactory;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;

class TestCase extends BaseTestCase
{
    protected function createConfig($providerMetadata = [], $clientMetadata = []): Config
    {
        return new Config(
            $this->createProviderMetadata($providerMetadata),
            $this->createClientMetadata($clientMetadata)
        );
    }

    protected function createClientMetadata($overwrite = []): ClientMetadata
    {
        return new ClientMetadata($this->createClientMetadataConfig($overwrite));
    }

    protected function createClientMetadataConfig($overwrite = []): array
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
            new \OpenIDConnect\Jwt\JwkSet(JsonConverter::decode(JsonConverter::encode($this->createJwkSet($jwks))))
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
