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
     * The browser submits the hidden inputs as an application/x-www-form-urlencoded body, so the
     * inputs are built from the same name and value pairs as the query of the redirect. The names,
     * the values and the action URI are escaped, since they can come from the application or from
     * the provider metadata.
     *
     * @link https://openid.net/specs/openid-connect-core-1_0.html#FormSerialization
     *
     * @param string $uri
     * @param array<string, mixed> $parameters
     * @return string
     */
    protected function generateFormPostHtml(string $uri, array $parameters = []): string
    {
        $formInput = implode('', array_map(function (array $field) {
            return '<input type="hidden" name="' . Html::escape($field[0])
                . '" value="' . Html::escape($field[1]) . '"/>';
        }, $this->formFields($parameters)));

        $action = Html::escape($uri);

        return <<< HTML
<!DOCTYPE html>
<html>
<head><title>Requesting Authorization</title></head>
<body onload="javascript:document.forms[0].submit()">
<form method="post" action="{$action}">{$formInput}</form>
</body>
</html>
HTML;
    }

    /**
     * Name and value pairs of the parameters, flattened in the same way as the query of the redirect
     *
     * @param array<string, mixed> $parameters
     * @return array<array{string, string}>
     */
    private function formFields(array $parameters): array
    {
        $query = Query::build($parameters);

        if ($query === '') {
            return [];
        }

        return array_map(static function (string $pair): array {
            [$name, $value] = array_pad(explode('=', $pair, 2), 2, '');

            return [rawurldecode($name), rawurldecode($value)];
        }, explode('&', $query));
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
