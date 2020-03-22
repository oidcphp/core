<?php

declare(strict_types=1);

namespace OpenIDConnect\OAuth2\Utils;

/**
 * General query builder and parser for process query and post body
 *
 * @link https://tools.ietf.org/html/rfc3986
 */
class Query
{
    /**
     * Generate URL-encoded query string default by PHP_QUERY_RFC3986
     *
     * @param array<mixed> $params
     * @param int $encType
     * @return string
     */
    public static function build(array $params, int $encType = PHP_QUERY_RFC3986): string
    {
        return http_build_query($params, '', '&', $encType);
    }

    /**
     * Parse URL-encoded query string to PHP array
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
