<?php

declare(strict_types=1);

namespace OpenIDConnect\Http;

/**
 * Query string builder and parser for URIs and form bodies
 *
 * @internal
 */
class Query
{
    /**
     * Build the query component of a URI, such as the query of a redirect
     *
     * Encoded per RFC 3986, so a space is encoded as `%20`.
     *
     * @link https://www.rfc-editor.org/rfc/rfc3986.html#section-3.4
     *
     * @param array<mixed> $params
     * @return string
     */
    public static function buildUriQuery(array $params): string
    {
        return http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Build an application/x-www-form-urlencoded body, such as the body of a token request
     *
     * Encoded with the escaping rules of HTML 4.01, so a space is encoded as `+`.
     *
     * @link https://www.rfc-editor.org/rfc/rfc6749.html#appendix-B
     *
     * @param array<mixed> $params
     * @return string
     */
    public static function buildFormBody(array $params): string
    {
        return http_build_query($params, '', '&', PHP_QUERY_RFC1738);
    }

    /**
     * Flatten the parameters to name and value pairs, such as the hidden inputs of a form post
     *
     * The pairs follow the same rules as the builders: an array is flattened to `scope[0]`,
     * `scope[1]`, ..., `null` is skipped, and `true` and `false` become `1` and `0`. The names and
     * the values are not encoded.
     *
     * @param array<mixed> $params
     * @return array<array{string, string}>
     */
    public static function buildPairs(array $params): array
    {
        // PHP has no function to flatten the parameters without encoding them, so the RFC 3986
        // query is split and decoded with rawurldecode(), which reverses that encoding exactly.
        $query = self::buildUriQuery($params);

        if ($query === '') {
            return [];
        }

        return array_map(static function (string $pair): array {
            [$name, $value] = array_pad(explode('=', $pair, 2), 2, '');

            return [rawurldecode($name), rawurldecode($value)];
        }, explode('&', $query));
    }

    /**
     * Parse a URI query or an application/x-www-form-urlencoded body to PHP array
     *
     * Both `+` and `%20` are decoded to a space, so it parses the output of both builders.
     *
     * @param string $queryString
     * @return array<mixed>
     */
    public static function parse(string $queryString): array
    {
        parse_str($queryString, $parsed);

        return $parsed;
    }
}
