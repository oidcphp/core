<?php

declare(strict_types=1);

namespace OpenIDConnect\Http;

use MilesChou\Psr\Http\Client\HttpClientAwareTrait;
use MilesChou\Psr\Http\Client\HttpClientInterface;
use OpenIDConnect\Config;
use OpenIDConnect\Traits\ConfigAwareTrait;

abstract class Builder
{
    use ConfigAwareTrait;
    use HttpClientAwareTrait;

    /**
     * @param Config $config
     * @param HttpClientInterface $httpClient
     */
    public function __construct(Config $config, HttpClientInterface $httpClient)
    {
        $this->setConfig($config);
        $this->setHttpClient($httpClient);
    }
}
