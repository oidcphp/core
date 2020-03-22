<?php

declare(strict_types=1);

namespace OpenIDConnect\OAuth2\Builder;

use OpenIDConnect\OAuth2\Utils\Query;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

/**
 * @see https://tools.ietf.org/html/rfc6749#section-4.1.1
 * @see https://tools.ietf.org/html/rfc6749#section-4.2.1
 */
class AuthorizationRedirectResponseBuilder
{
    use BuilderTrait;

    /**
     * @param array<mixed> $parameters
     * @return ResponseInterface
     */
    public function build(array $parameters): ResponseInterface
    {
        /** @var ResponseFactoryInterface $responseFactory */
        $responseFactory = $this->container->get(ResponseFactoryInterface::class);

        return $responseFactory->createResponse(302)
            ->withHeader('Location', (string)$this->createAuthorizeUri($parameters));
    }

    /**
     * @param array<mixed> $parameters
     * @return UriInterface
     */
    private function createAuthorizeUri(array $parameters): UriInterface
    {
        /** @var UriFactoryInterface $uriFactory */
        $uriFactory = $this->container->get(UriFactoryInterface::class);

        return $uriFactory->createUri($this->providerMetadata->require('authorization_endpoint'))
            ->withQuery(Query::build($parameters));
    }
}
