<?php

declare(strict_types=1);

namespace OpenIDConnect\Http\Request;

use MilesChou\Psr\Http\Message\PendingRequest;
use OpenIDConnect\Http\Authentication\ClientAuthenticationAwareTrait;
use OpenIDConnect\Http\Builder;
use OpenIDConnect\Http\Query;
use OpenIDConnect\OAuth2\Grant\GrantType;

/**
 * Generate Request for token endpoint
 *
 * @see https://tools.ietf.org/html/rfc6749#section-3.2
 */
class TokenRequestBuilder extends Builder
{
    use ClientAuthenticationAwareTrait;

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
