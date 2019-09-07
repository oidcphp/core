<?php

namespace OpenIDConnect\Http;

use Psr\Http\Message\RequestInterface;
use function GuzzleHttp\Psr7\stream_for;

class TokenRequestFactory
{
    use QueryProcessorTrait;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @param string $endpoint
     */
    public function __construct(string $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    public function createRequest(array $parameters): RequestInterface
    {
        return (new DefaultRequestFactory())->createRequest('POST', $this->endpoint)
            ->withHeader('content-type', 'application/x-www-form-urlencoded')
            ->withBody(stream_for($this->buildQueryString($parameters)));
    }
}
