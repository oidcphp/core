<?php

declare(strict_types=1);

namespace OpenIDConnect;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\ClientInterface as HttpClientInterface;
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
class Factory
{
    use MetadataAwareTraits;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ProviderMetadata $provider
     * @param ClientRegistration $client
     * @param ContainerInterface|null $container
     */
    public function __construct(
        ProviderMetadata $provider,
        ClientRegistration $client,
        ContainerInterface $container = null
    ) {
        $this->setProviderMetadata($provider);
        $this->setClientRegistration($client);

        if (null === $container) {
            $this->container = new Container([
                GrantFactory::class => new GrantFactory(),
                HttpClientInterface::class => new HttpClient(),
            ]);
        }
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
}
