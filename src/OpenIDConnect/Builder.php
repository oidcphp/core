<?php

declare(strict_types=1);

namespace OpenIDConnect;

use OpenIDConnect\Container\Container;
use OpenIDConnect\Metadata\ClientRegistration;
use OpenIDConnect\Metadata\MetadataAwareTraits;
use OpenIDConnect\Metadata\ProviderMetadata;
use OpenIDConnect\OAuth2\Grant\GrantFactory;
use OpenIDConnect\Token\TokenSet;
use Psr\Container\ContainerInterface;

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
        return $this->setContainer(new Container([
            GrantFactory::class => new GrantFactory(),
            \GuzzleHttp\ClientInterface::class => new \GuzzleHttp\Client(),
            \Psr\Http\Message\StreamFactoryInterface::class => new \Http\Factory\Guzzle\StreamFactory(),
            \Psr\Http\Message\ResponseFactoryInterface::class => new \Http\Factory\Guzzle\ResponseFactory(),
            \Psr\Http\Message\RequestFactoryInterface::class => new \Http\Factory\Guzzle\RequestFactory(),
            \Psr\Http\Message\UriFactoryInterface::class => new \Http\Factory\Guzzle\UriFactory(),
        ]));
    }
}
