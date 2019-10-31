<?php

namespace OpenIDConnect\Core\ClientAuthentication;

use OpenIDConnect\Support\Http\Query;
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
    public function withClientAuthentication(Request $request, string $client, string $secret): Request
    {
        $body = (string)$request->getBody();

        $parsedBody = Query::parse($body);
        $parsedBody['client_id'] = $client;
        $parsedBody['client_secret'] = $secret;

        return $request->withBody(stream_for(Query::build($parsedBody)));
    }
}
