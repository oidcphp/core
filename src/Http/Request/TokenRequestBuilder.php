<?php

declare(strict_types=1);

namespace OpenIDConnect\Http\Request;

use MilesChou\Psr\Http\Message\PendingRequest;
use OpenIDConnect\Http\Builder;
use OpenIDConnect\Http\Query;
use OpenIDConnect\OAuth2\ClientAuthentication\ClientAuthenticationAwareTrait;
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
    public function build(GrantType $grantType, array $parameters): PendingRequest
    {
        $clientAuthentication = $this->resolveClientAuthenticationByDefault(
            $this->clientInformation->id(),
            $this->clientInformation->secret()
        );

        $parameters = $grantType->prepareTokenRequestParameters($parameters);

        $request = $this->httpFactory->createRequest('POST', $this->providerMetadata->require('token_endpoint'))
            ->withHeader('content-type', 'application/x-www-form-urlencoded')
            ->withBody($this->httpFactory->createStream(Query::build($parameters)));

        $request = $clientAuthentication->processRequest($request);

        return new PendingRequest($request, $this->httpClient);
    }
}
