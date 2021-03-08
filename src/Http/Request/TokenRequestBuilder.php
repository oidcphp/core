<?php

declare(strict_types=1);

namespace OpenIDConnect\Http\Request;

use MilesChou\Psr\Http\Client\HttpClientAwareTrait;
use MilesChou\Psr\Http\Client\HttpClientInterface;
use MilesChou\Psr\Http\Message\PendingRequest;
use OpenIDConnect\Config;
use OpenIDConnect\Http\Authentication\ClientAuthenticationAwareTrait;
use OpenIDConnect\Http\Query;
use OpenIDConnect\OAuth2\Grant\GrantType;
use OpenIDConnect\Traits\ConfigAwareTrait;

/**
 * Generate Request for token endpoint
 *
 * @see https://tools.ietf.org/html/rfc6749#section-3.2
 */
class TokenRequestBuilder
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
     * @param GrantType $grantType
     * @param array<mixed> $parameters
     * @return PendingRequest
     */
    public function build(array $parameters, GrantType $grantType): PendingRequest
    {
        $clientAuthentication = $this->resolveClientAuthentication(
            $this->config->requireClientMetadata('client_id'),
            $this->config->requireClientMetadata('client_secret')
        );

        $parameters = $grantType->prepareTokenRequestParameters($parameters);

        $uri = $this->config->providerMetadata()->require('token_endpoint');

        $request = $this->httpClient->createRequest('POST', $uri)
            ->withHeader('content-type', 'application/x-www-form-urlencoded')
            ->withBody($this->httpClient->createStream(Query::build($parameters)));

        $request = $clientAuthentication->processRequest($request);

        return new PendingRequest($request, $this->httpClient);
    }
}
