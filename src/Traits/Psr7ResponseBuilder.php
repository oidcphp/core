<?php

namespace OpenIDConnect\Traits;

use MilesChou\Psr\Http\Client\HttpClientAwareTrait;
use OpenIDConnect\Http\Query;
use Psr\Http\Message\UriInterface;

trait Psr7ResponseBuilder
{
    use ConfigAwareTrait;
    use HttpClientAwareTrait;

    /**
     * Using form post
     *
     * @param string $uri
     * @param array $parameters
     * @return string
     */
    protected function generateFormPostHtml(string $uri, array $parameters = []): string
    {
        $formInput = implode('', array_map(function ($value, $key) {
            return "<input type=\"hidden\" name=\"{$key}\" value=\"{$value}\"/>";
        }, $parameters, array_keys($parameters)));

        return <<< HTML
<!DOCTYPE html>
<head><title>Requesting Authorization</title></head>
<body onload="javascript:document.forms[0].submit()">
<form method="post" action="{$uri}">{$formInput}</form>
</body>
</html>
HTML;
    }

    /**
     * @param string $key
     * @param array $parameters
     * @return string
     */
    protected function generateFormPostHtmlWithProviderConfig(string $key, array $parameters = []): string
    {
        return $this->generateFormPostHtml($this->config->requireProviderMetadata($key), $parameters);
    }

    /**
     * @param string $uri
     * @param array $parameters
     * @return UriInterface
     */
    protected function generateRedirectUri(string $uri, array $parameters = []): UriInterface
    {
        return $this->httpClient->createUri($uri)
            ->withQuery(Query::build($parameters));
    }

    /**
     * @param string $key
     * @param array $parameters
     * @return UriInterface
     */
    protected function generateRedirectUriWithProviderConfig(string $key, array $parameters = []): UriInterface
    {
        return $this->generateRedirectUri($this->config->requireProviderMetadata($key), $parameters);
    }
}
