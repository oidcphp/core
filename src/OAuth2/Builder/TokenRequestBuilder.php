<?php

declare(strict_types=1);

namespace OpenIDConnect\OAuth2\Builder;

use OpenIDConnect\OAuth2\ClientAuthentication\ClientAuthenticationAwareTrait;
use OpenIDConnect\OAuth2\Grant\GrantType;
use OpenIDConnect\OAuth2\Utils\Query;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Generate Request for token endpoint
 *
 * @see https://tools.ietf.org/html/rfc6749#section-3.2
 */
class TokenRequestBuilder
{
    use BuilderTrait;
    use ClientAuthenticationAwareTrait;

    /**
     * @param GrantType $grantType
     * @param array<mixed> $parameters
     * @return RequestInterface
     */
    public function build(GrantType $grantType, array $parameters): RequestInterface
    {
        $parameters = $grantType->prepareTokenRequestParameters($parameters);

        /** @var RequestFactoryInterface $requestFactory */
        $requestFactory = $this->container->get(RequestFactoryInterface::class);

        /** @var StreamFactoryInterface $streamFactory */
        $streamFactory = $this->container->get(StreamFactoryInterface::class);

        $request = $requestFactory->createRequest('POST', $this->providerMetadata->require('token_endpoint'))
            ->withHeader('content-type', 'application/x-www-form-urlencoded')
            ->withBody($streamFactory->createStream(Query::build($parameters)));

        $clientAuthentication = $this->resolveClientAuthenticationByDefault(
            $this->clientInformation->id(),
            $this->clientInformation->secret()
        );

        return $clientAuthentication->processRequest($request);
    }
}
