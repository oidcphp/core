<?php

declare(strict_types=1);

namespace OpenIDConnect\Core;

use OpenIDConnect\Config\ClientInformation;
use OpenIDConnect\Config\ProviderMetadata;
use OpenIDConnect\OAuth2\Metadata\ClientInformationAwaitTrait;
use OpenIDConnect\OAuth2\Metadata\ProviderMetadataAwaitTrait;
use Psr\Container\ContainerInterface;

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
     * @param ProviderMetadata $provider
     * @param ClientInformation $client
     */
    public function __construct(ProviderMetadata $provider, ClientInformation $client)
    {
        $this->setProviderMetadata($provider);
        $this->setClientInformation($client);
    }
}
