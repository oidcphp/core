<?php

namespace OpenIDConnect\Traits;

use MilesChou\Psr\Http\Client\HttpClientAwareTrait;
use OpenIDConnect\Http\Html;
use OpenIDConnect\Http\Query;
use Psr\Http\Message\UriInterface;

trait Psr7ResponseBuilder
{
    use ConfigAwareTrait;
    use HttpClientAwareTrait;

    /**
     * Using form post
     *
     * @see \OpenIDConnect\Http\Html::formPost()
     *
     * @param string $uri
     * @param array<string, mixed> $parameters
     * @return string
     */
    protected function generateFormPostHtml(string $uri, array $parameters = []): string
    {
        return Html::formPost($uri, $parameters);
    }

    /**
     * @param string $key
     * @param array<string, mixed> $parameters
     * @return string
     */
    protected function generateFormPostHtmlWithProviderConfig(string $key, array $parameters = []): string
    {
        return $this->generateFormPostHtml($this->config->requireProviderMetadata($key), $parameters);
    }

    /**
     * @param string $uri
     * @param array<string, mixed> $parameters
     * @return UriInterface
     */
    protected function generateRedirectUri(string $uri, array $parameters = []): UriInterface
    {
        return $this->httpClient->createUri($uri)
            ->withQuery(Query::buildUriQuery($parameters));
    }

    /**
     * @param string $key
     * @param array<string, mixed> $parameters
     * @return UriInterface
     */
    protected function generateRedirectUriWithProviderConfig(string $key, array $parameters = []): UriInterface
    {
        return $this->generateRedirectUri($this->config->requireProviderMetadata($key), $parameters);
    }
}
