<?php

namespace OpenIDConnect\Core\OAuth2;

use GuzzleHttp\Psr7\Request;
use OpenIDConnect\Support\Http\Query;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use function GuzzleHttp\Psr7\stream_for;

/**
 * Generate Request for token endpoint
 */
class TokenRequestFactory implements RequestFactoryInterface
{
    /**
     * @var array
     */
    private $parameters;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    public function createRequest(string $method, $uri): RequestInterface
    {
        return (new Request($method, $uri))
            ->withHeader('content-type', 'application/x-www-form-urlencoded')
            ->withBody(stream_for(Query::build($this->parameters)));
    }
}
