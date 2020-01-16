<?php

declare(strict_types=1);

namespace OpenIDConnect\Core;

use GuzzleHttp\Client as HttpClient;
use Http\Factory\Guzzle\RequestFactory;
use Http\Factory\Guzzle\ResponseFactory;
use Http\Factory\Guzzle\StreamFactory;
use Http\Factory\Guzzle\UriFactory;
use OpenIDConnect\Core\Token\TokenFactory;
use OpenIDConnect\OAuth2\Metadata\ClientInformation;
use OpenIDConnect\OAuth2\Metadata\ClientInformationAwaitTrait;
use OpenIDConnect\OAuth2\Metadata\ProviderMetadata;
use OpenIDConnect\OAuth2\Metadata\ProviderMetadataAwaitTrait;
use OpenIDConnect\OAuth2\Token\TokenFactoryInterface;
use OpenIDConnect\Support\Container\Container;
use OpenIDConnect\Support\Http\GuzzlePsr18Client;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

/**
 * Factory for create anything
 */
class Builder
{
    use ProviderMetadataAwaitTrait;
    use ClientInformationAwaitTrait;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ProviderMetadata $provider
     * @param ClientInformation $client
     * @return static
     */
    public static function create(ProviderMetadata $provider, ClientInformation $client): Builder
    {
        return new static($provider, $client);
    }

    /**
     * @param array $instances
     * @return Container
     */
    public static function createDefaultContainer($instances = []): Container
    {
        if (empty($instances[ClientInterface::class])) {
            $instances[ClientInterface::class] = new GuzzlePsr18Client(new HttpClient());
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

        if (empty($instances[TokenFactoryInterface::class])) {
            $instances[TokenFactoryInterface::class] = new TokenFactory();
        }

        return new Container($instances);
    }

    /**
     * @param ProviderMetadata $provider
     * @param ClientInformation $client
     */
    public function __construct(ProviderMetadata $provider, ClientInformation $client)
    {
        $this->setProviderMetadata($provider);
        $this->setClientInformation($client);
    }

    /**
     * @return Client
     */
    public function createOpenIDConnectClient(): Client
    {
        return new Client($this->providerMetadata, $this->clientInformation, $this->container);
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
