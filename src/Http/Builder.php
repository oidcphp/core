<?php

declare(strict_types=1);

namespace OpenIDConnect\Http;

use MilesChou\Psr\Http\Client\HttpClientAwareTrait;
use MilesChou\Psr\Http\Message\HttpFactoryAwareTrait;
use MilesChou\Psr\Http\Message\HttpFactoryInterface;
use OpenIDConnect\Metadata\ClientInformationAwareTrait;
use OpenIDConnect\Metadata\ProviderMetadataAwareTrait;
use Psr\Http\Client\ClientInterface;

abstract class Builder
{
    use HttpClientAwareTrait;
    use HttpFactoryAwareTrait;
    use ProviderMetadataAwareTrait;
    use ClientInformationAwareTrait;

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
