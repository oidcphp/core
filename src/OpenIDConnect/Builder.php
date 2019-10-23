<?php

declare(strict_types=1);

namespace OpenIDConnect;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\ClientInterface;
use Http\Factory\Guzzle\RequestFactory;
use Http\Factory\Guzzle\ResponseFactory;
use Http\Factory\Guzzle\StreamFactory;
use Http\Factory\Guzzle\UriFactory;
use OpenIDConnect\Container\Container;
use OpenIDConnect\Exceptions\EntryNotFoundException;
use OpenIDConnect\Metadata\ClientRegistration;
use OpenIDConnect\Metadata\MetadataAwareTraits;
use OpenIDConnect\Metadata\ProviderMetadata;
use OpenIDConnect\OAuth2\Grant\GrantFactory;
use OpenIDConnect\Token\TokenSet;
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
        $entries = [
            GrantFactory::class,
            ClientInterface::class,
            StreamFactoryInterface::class,
            ResponseFactoryInterface::class,
            RequestFactoryInterface::class,
            UriFactoryInterface::class,
        ];

        foreach ($entries as $entry) {
            if (!$container->has($entry)) {
                throw new EntryNotFoundException("The entry '$entry' is not found");
            }
        }

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
            ClientInterface::class => new HttpClient(),
            StreamFactoryInterface::class => new StreamFactory(),
            ResponseFactoryInterface::class => new ResponseFactory(),
            RequestFactoryInterface::class => new RequestFactory(),
            UriFactoryInterface::class => new UriFactory(),
        ]));
    }
}
