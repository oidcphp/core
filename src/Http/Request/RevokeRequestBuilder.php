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
 * @link https://www.rfc-editor.org/rfc/rfc7009.html#section-2.1
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

        // See https://www.rfc-editor.org/rfc/rfc8414.html#section-2
        $uri = $this->config->requireProviderMetadata('revocation_endpoint');

        $request = $this->httpClient->createRequest('POST', $uri)
            ->withHeader('content-type', 'application/x-www-form-urlencoded')
            ->withBody($this->httpClient->createStream(Query::build($parameters)));

        $request = $clientAuthentication->processRequest($request);

        return new PendingRequest($request, $this->httpClient);
    }
}
