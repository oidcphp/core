<?php

declare(strict_types=1);

namespace OpenIDConnect\Core;

use OpenIDConnect\Metadata\ClientInformation;
use OpenIDConnect\Metadata\ClientInformationAwareTrait;
use OpenIDConnect\Metadata\ProviderMetadata;
use OpenIDConnect\Metadata\ProviderMetadataAwareTrait;
use Psr\Container\ContainerInterface;

/**
 * Factory for create anything
 */
class Builder
{
    use ProviderMetadataAwareTrait;
    use ClientInformationAwareTrait;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ProviderMetadata $provider
     * @param \OpenIDConnect\Metadata\ClientInformation $client
     *
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
