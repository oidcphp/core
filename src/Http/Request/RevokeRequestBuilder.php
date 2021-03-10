<?php

declare(strict_types=1);

namespace OpenIDConnect\Http\Request;

use MilesChou\Psr\Http\Client\HttpClientAwareTrait;
use MilesChou\Psr\Http\Client\HttpClientInterface;
use MilesChou\Psr\Http\Message\PendingRequest;
use OpenIDConnect\Config;
use OpenIDConnect\Http\Authentication\ClientAuthenticationAwareTrait;
use OpenIDConnect\Http\Query;
use OpenIDConnect\Traits\ConfigAwareTrait;

/**
 * Generate Request for revocation endpoint
 *
 * @see https://tools.ietf.org/html/rfc7009#section-2.1
 */
class RevokeRequestBuilder
{
    use ClientAuthenticationAwareTrait;
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

    /**
     * @param array $parameters
     * @return PendingRequest
     */
    public function build(array $parameters): PendingRequest
    {
        $clientAuthentication = $this->resolveClientAuthentication(
            $this->config->requireClientMetadata('client_id'),
            $this->config->requireClientMetadata('client_secret')
        );

        // See https://tools.ietf.org/html/rfc8414#section-2
        $uri = $this->config->requireProviderMetadata('revocation_endpoint');

        $request = $this->httpClient->createRequest('POST', $uri)
            ->withHeader('content-type', 'application/x-www-form-urlencoded')
            ->withBody($this->httpClient->createStream(Query::build($parameters)));

        $request = $clientAuthentication->processRequest($request);

        return new PendingRequest($request, $this->httpClient);
    }
}
