<?php

declare(strict_types=1);

namespace OpenIDConnect;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\ClientInterface as HttpClientInterface;
use OpenIDConnect\Container\Container;
use OpenIDConnect\Metadata\ClientMetadata as ClientMeta;
use OpenIDConnect\Metadata\MetadataAwareTraits;
use OpenIDConnect\Metadata\ProviderMetadata as ProviderMeta;
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
     * @param ProviderMeta $provider
     * @param ClientMeta $client
     * @param ContainerInterface|null $container
     */
    public function __construct(ProviderMeta $provider, ClientMeta $client, ContainerInterface $container = null)
    {
        $this->setProviderMetadata($provider);
        $this->setClientMetadata($client);

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
        return new Client($this->providerMetadata, $this->clientMetadata, $this->container);
    }

    /**
     * @param array $parameters
     * @return TokenSet
     */
    public function createTokenSet(array $parameters): TokenSet
    {
        return new TokenSet($parameters, $this->providerMetadata, $this->clientMetadata);
    }
}
