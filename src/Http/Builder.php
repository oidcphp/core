<?php

declare(strict_types=1);

namespace OpenIDConnect\Http;

use MilesChou\Psr\Http\Client\HttpClientAwareTrait;
use MilesChou\Psr\Http\Message\HttpFactoryAwareTrait;
use MilesChou\Psr\Http\Message\HttpFactoryInterface;
use OpenIDConnect\OAuth2\Metadata\ClientInformationAwaitTrait;
use OpenIDConnect\OAuth2\Metadata\ProviderMetadataAwaitTrait;
use Psr\Http\Client\ClientInterface;

abstract class Builder
{
    use HttpClientAwareTrait;
    use HttpFactoryAwareTrait;
    use ProviderMetadataAwaitTrait;
    use ClientInformationAwaitTrait;

    /**
     * @param ClientInterface $httpClient
     * @param HttpFactoryInterface $httpFactory
     */
    public function __construct(ClientInterface $httpClient, HttpFactoryInterface $httpFactory)
    {
        $this->setHttpClient($httpClient);
        $this->setHttpFactory($httpFactory);
    }
}
