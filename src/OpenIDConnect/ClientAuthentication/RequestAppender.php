<?php

namespace OpenIDConnect\ClientAuthentication;

use Psr\Http\Message\RequestInterface as Request;

/**
 * @see https://tools.ietf.org/html/rfc6749#section-2.3
 */
interface RequestAppender
{
    /**
     * @param Request $request
     * @param string $client
     * @param string $secret
     * @return Request
     */
    public function withClientAuthentication(Request $request, string $client, string $secret): Request;
}
