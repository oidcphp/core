<?php

declare(strict_types=1);

namespace OpenIDConnect\Http\Response;

use OpenIDConnect\Http\Builder;
use Psr\Http\Message\ResponseInterface;

class AuthorizationFormPostResponseBuilder extends Builder
{
    /**
     * @param array $parameters
     * @return ResponseInterface
     */
    public function build(array $parameters): ResponseInterface
    {
        $body = $this->httpClient->createStream(
            $this->generateFormPostHtmlWithProviderConfig('authorization_endpoint', $parameters)
        );

        return $this->httpClient->createResponse()
            ->withBody($body);
    }
}
