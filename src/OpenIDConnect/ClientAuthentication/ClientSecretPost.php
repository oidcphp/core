<?php

namespace OpenIDConnect\ClientAuthentication;

use OpenIDConnect\Http\QueryProcessorTrait;
use Psr\Http\Message\RequestInterface as Request;
use function GuzzleHttp\Psr7\stream_for;

/**
 * Client credentials in the request-body
 *
 * NOT RECOMMENDED
 *
 * @see https://tools.ietf.org/html/rfc6749#section-2.3.1
 */
class ClientSecretPost implements RequestAppender
{
    use QueryProcessorTrait;

    public function withClientAuthentication(Request $request, string $client, string $secret): Request
    {
        $body = (string)$request->getBody();

        $parsedBody = $this->parseQueryString($body);
        $parsedBody['client_id'] = $client;
        $parsedBody['client_secret'] = $secret;

        return $request->withBody(stream_for($this->buildQueryString($parsedBody)));
    }
}
