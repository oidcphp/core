<?php

namespace OpenIDConnect;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class Client
{
    /**
     * @var ProviderMetadata
     */
    private $providerMetadata;

    /**
     * @var ClientMetadata
     */
    private $clientMetadata;

    /**
     * @param ProviderMetadata $providerMetadata
     * @param ClientMetadata $clientMetadata
     */
    public function __construct(ProviderMetadata $providerMetadata, ClientMetadata $clientMetadata)
    {
        $this->providerMetadata = $providerMetadata;
        $this->clientMetadata = $clientMetadata;
    }

    /**
     * @param array $params
     * @return UriInterface
     */
    public function authorizationUrl(array $params = []): UriInterface
    {
        $endpoint = $this->providerMetadata->authorizationEndpoint();

        $params = array_merge([
            'client_id' => $this->clientMetadata->id(),
            'scope' => 'openid',
            'response_type' => 'code',
        ], $params);

        return Uri::withQueryValues(new Uri($endpoint), $params);
    }

    /**
     * @param array $params
     * @param int $status
     * @return ResponseInterface
     */
    public function authorizationResponse(array $params = [], int $status = 302): ResponseInterface
    {
        $uri = $this->authorizationUrl($params);

        $response = new Response($status);

        if (302 === $status) {
            return $response->withHeader('Location', (string)$uri);
        }

        return $response;
    }
}
