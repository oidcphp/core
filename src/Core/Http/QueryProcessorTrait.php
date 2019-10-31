<?php

namespace OpenIDConnect\Core\Http;

/**
 * General query builder and parser for process query and post body
 */
trait QueryProcessorTrait
{
    /**
     * @param array $params
     * @param int $encType
     * @return string
     */
    protected function buildQueryString(array $params, int $encType = PHP_QUERY_RFC3986): string
    {
        return http_build_query($params, '', '&', $encType);
    }

    /**
     * @param string $queryString
     * @return array
     */
    protected function parseQueryString(string $queryString): array
    {
        parse_str($queryString, $parsed);

        return $parsed;
    }
}
