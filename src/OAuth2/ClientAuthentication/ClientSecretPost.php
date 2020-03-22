<?php

declare(strict_types=1);

namespace OpenIDConnect\OAuth2\ClientAuthentication;

use OpenIDConnect\OAuth2\Utils\Query;
use Psr\Http\Message\RequestInterface;

/**
 * @see https://tools.ietf.org/html/rfc6749#section-2.3.1
 */
class ClientSecretPost implements ClientAuthentication
{
    /**
     * @var string
     */
    private $client;

    /**
     * @var string
     */
    private $secret;

    /**
     * @param string $client
     * @param string $secret
     */
    public function __construct(string $client, string $secret)
    {
        $this->client = $client;
        $this->secret = $secret;
    }

    /**
     * @inheritDoc
     */
    public function processRequest(RequestInterface $request): RequestInterface
    {
        $body = $request->getBody();

        $parsedBody = Query::parse((string)$body);
        $parsedBody['client_id'] = $this->client;
        $parsedBody['client_secret'] = $this->secret;

        $body->rewind();
        $body->write(Query::build($parsedBody) . "\0");

        return $request->withBody($body);
    }
}
