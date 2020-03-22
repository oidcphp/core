<?php

declare(strict_types=1);

namespace OpenIDConnect\OAuth2\ClientAuthentication;

use Psr\Http\Message\RequestInterface;

/**
 * Default method for client authentication
 *
 * @see https://tools.ietf.org/html/rfc6749#section-2.3.1
 */
class ClientSecretBasic implements ClientAuthentication
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
        $encodedCredentials = base64_encode(sprintf('%s:%s', $this->client, $this->secret));

        return $request->withHeader('Authorization', 'Basic ' . $encodedCredentials);
    }
}
