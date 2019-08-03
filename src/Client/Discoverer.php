<?php

namespace OpenIDConnect\Client;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface as HttpClientInterface;
use Psr\Http\Message\ResponseInterface;
use UnexpectedValueException;
use function GuzzleHttp\json_decode;

/**
 * OpenID Connect provider discoverer
 */
class Discoverer
{
    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @see https://openid.net/specs/openid-connect-discovery-1_0.html
     */
    public const OPENID_CONNECT_DISCOVERY = '/.well-known/openid-configuration';

    /**
     * @param array $httpOption
     */
    public function __construct(array $httpOption = [])
    {
        $this->httpClient = new Client($httpOption);
    }

    /**
     * Discover the OpenID Connect provider
     *
     * @param string $url
     * @return array
     */
    public function discover(string $url): array
    {
        $discoveryUri = $url . self::OPENID_CONNECT_DISCOVERY;

        $response = $this->httpClient->request('GET', $discoveryUri);

        return $this->processResponse($response);
    }

    /**
     * @param HttpClientInterface $httpClient
     */
    public function setHttpClient(HttpClientInterface $httpClient): void
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param ResponseInterface $response
     * @return array
     */
    private function processResponse(ResponseInterface $response): array
    {
        if (200 !== $response->getStatusCode()) {
            throw new UnexpectedValueException('Server Error');
        }

        return json_decode((string)$response->getBody(), true);
    }
}
