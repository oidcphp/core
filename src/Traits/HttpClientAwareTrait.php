<?php

namespace OpenIDConnect\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

/**
 * HttpClient aware trait
 */
trait HttpClientAwareTrait
{
    /**
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * Implements get the Guzzle Client.
     *
     * @param array $config
     * @return ClientInterface
     */
    public function getHttpClient(array $config = []): ClientInterface
    {
        if (null === $this->httpClient) {
            $this->httpClient = new Client($config);
        }

        return $this->httpClient;
    }

    /**
     * Implements set the HTTP Client.
     *
     * @param ClientInterface $httpClient
     * @return static
     */
    public function setHttpClient(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;

        return $this;
    }
}
