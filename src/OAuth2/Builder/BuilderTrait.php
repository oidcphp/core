<?php

declare(strict_types=1);

namespace OpenIDConnect\OAuth2\Builder;

use OpenIDConnect\OAuth2\Metadata\ClientInformationAwaitTrait;
use OpenIDConnect\OAuth2\Metadata\ProviderMetadataAwaitTrait;
use Psr\Container\ContainerInterface;

trait BuilderTrait
{
    use ProviderMetadataAwaitTrait;
    use ClientInformationAwaitTrait;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
