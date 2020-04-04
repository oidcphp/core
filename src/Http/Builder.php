<?php

declare(strict_types=1);

namespace OpenIDConnect\Http;

use MilesChou\Psr\Http\Client\HttpClientAwareTrait;
use MilesChou\Psr\Http\Client\HttpClientInterface;
use OpenIDConnect\Contracts\ConfigInterface;
use OpenIDConnect\Traits\ConfigAwareTrait;

abstract class Builder
{
    use ConfigAwareTrait;
    use HttpClientAwareTrait;

    /**
     * @param ConfigInterface $config
     * @param HttpClientInterface $httpClient
     */
    public function __construct(ConfigInterface $config, HttpClientInterface $httpClient)
    {
        $this->setConfig($config);
        $this->setHttpClient($httpClient);
    }
}
