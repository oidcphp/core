<?php

namespace Tests;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response as HttpResponse;
use Psr\Http\Message\ResponseInterface;
use function GuzzleHttp\json_encode;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Creates HTTP client.
     *
     * @param ResponseInterface|ResponseInterface[] $responses
     * @param array $history
     * @return HandlerStack
     */
    public function createHandlerStack($responses = [], &$history = []): HandlerStack
    {
        if (!is_array($responses)) {
            $responses = [$responses];
        }

        $handler = HandlerStack::create(new MockHandler($responses));
        $handler->push(Middleware::history($history));

        return $handler;
    }

    /**
     * Creates HTTP client.
     *
     * @param ResponseInterface|ResponseInterface[] $responses
     * @param array $history
     * @return HttpClient
     */
    public function createHttpClient($responses = [], &$history = []): HttpClient
    {
        return new HttpClient($this->createHttpMockOption($responses, $history));
    }

    /**
     * @param array $data
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    public function createHttpJsonResponse(array $data = [], int $status = 200, array $headers = []): ResponseInterface
    {
        return new HttpResponse($status, $headers, json_encode($data));
    }

    /**
     * @param ResponseInterface|ResponseInterface[] $responses
     * @param array $history
     * @return array
     */
    public function createHttpMockOption($responses = [], &$history = []): array
    {
        return [
            'handler' => $this->createHandlerStack($responses, $history),
        ];
    }
}
