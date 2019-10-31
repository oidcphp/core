<?php

declare(strict_types=1);

namespace OpenIDConnect\Core;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\ClientInterface;
use Http\Factory\Guzzle\RequestFactory;
use Http\Factory\Guzzle\ResponseFactory;
use Http\Factory\Guzzle\StreamFactory;
use Http\Factory\Guzzle\UriFactory;
use OpenIDConnect\Core\Metadata\ClientRegistration;
use OpenIDConnect\Core\Metadata\MetadataAwareTraits;
use OpenIDConnect\Core\Metadata\ProviderMetadata;
use OpenIDConnect\Core\OAuth2\Grant\GrantFactory;
use OpenIDConnect\Core\Token\TokenSet;
use OpenIDConnect\Support\Container\Container;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

/**
 * Factory for create anything
 */
class Builder
{
    use MetadataAwareTraits;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ProviderMetadata $provider
     * @param ClientRegistration $client
     * @return static
     */
    public static function create(ProviderMetadata $provider, ClientRegistration $client): Builder
    {
        return new static($provider, $client);
    }

    /**
     * @param array $instances
     * @return Container
     */
    public static function createDefaultContainer($instances = []): Container
    {
        if (empty($instances[GrantFactory::class])) {
            $instances[GrantFactory::class] = new GrantFactory();
        }

        if (empty($instances[ClientInterface::class])) {
            $instances[ClientInterface::class] = new HttpClient();
        }

        if (empty($instances[StreamFactoryInterface::class])) {
            $instances[StreamFactoryInterface::class] = new StreamFactory();
        }

        if (empty($instances[ResponseFactoryInterface::class])) {
            $instances[ResponseFactoryInterface::class] = new ResponseFactory();
        }

        if (empty($instances[RequestFactoryInterface::class])) {
            $instances[RequestFactoryInterface::class] = new RequestFactory();
        }

        if (empty($instances[UriFactoryInterface::class])) {
            $instances[UriFactoryInterface::class] = new UriFactory();
        }

        return new Container($instances);
    }

    /**
     * @param ProviderMetadata $provider
     * @param ClientRegistration $client
     */
    public function __construct(ProviderMetadata $provider, ClientRegistration $client)
    {
        $this->setProviderMetadata($provider);
        $this->setClientRegistration($client);
    }

    /**
     * @return Client
     */
    public function createOpenIDConnectClient(): Client
    {
        return new Client($this->providerMetadata, $this->clientRegistration, $this->container);
    }

    /**
     * @param array $parameters
     * @return TokenSet
     */
    public function createTokenSet(array $parameters): TokenSet
    {
        return new TokenSet($parameters, $this->providerMetadata, $this->clientRegistration);
    }

    /**
     * @param ContainerInterface $container
     * @return static
     */
    public function setContainer(ContainerInterface $container): Builder
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @return static
     */
    public function useDefaultContainer(): Builder
    {
        return $this->setContainer(static::createDefaultContainer());
    }
}
