<?php

declare(strict_types=1);

namespace OpenIDConnect\Http;

use MilesChou\Psr\Http\Client\HttpClientAwareTrait;
use MilesChou\Psr\Http\Message\HttpFactoryAwareTrait;
use MilesChou\Psr\Http\Message\HttpFactoryInterface;
use OpenIDConnect\Contracts\ConfigInterface;
use OpenIDConnect\Traits\ConfigAwareTrait;
use Psr\Http\Client\ClientInterface;

abstract class Builder
{
    use HttpClientAwareTrait;
    use HttpFactoryAwareTrait;
    use ConfigAwareTrait;

    /**
     * @param ConfigInterface $config
     * @param ClientInterface $httpClient
     * @param HttpFactoryInterface $httpFactory
     */
    public function __construct(ConfigInterface $config, ClientInterface $httpClient, HttpFactoryInterface $httpFactory)
    {
        $this->setConfig($config);
        $this->setHttpClient($httpClient);
        $this->setHttpFactory($httpFactory);
    }
}
