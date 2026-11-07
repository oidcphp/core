<?php

namespace OpenIDConnect\Contracts;

use Psr\Http\Message\ResponseInterface;

interface ResponseBuilder
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function build(array $parameters): ResponseInterface;
}
