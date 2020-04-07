<?php

declare(strict_types=1);

namespace OpenIDConnect\Http\Response;

use OpenIDConnect\Http\Builder;
use OpenIDConnect\Http\Query;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * @see https://tools.ietf.org/html/rfc6749#section-4.1.1
 * @see https://tools.ietf.org/html/rfc6749#section-4.2.1
 */
class AuthorizationRedirectResponseBuilder extends Builder
{
    /**
     * @param array<mixed> $parameters
     * @return ResponseInterface
     */
    public function build(array $parameters): ResponseInterface
    {
        return $this->httpClient->createResponse(302)
            ->withHeader('Location', (string)$this->createAuthorizeUri($parameters));
    }

    /**
     * @param array<mixed> $parameters
     * @return UriInterface
     */
    private function createAuthorizeUri(array $parameters): UriInterface
    {
        return $this->httpClient->createUri($this->config->requireProviderMetadata('authorization_endpoint'))
            ->withQuery(Query::build($parameters));
    }
}
