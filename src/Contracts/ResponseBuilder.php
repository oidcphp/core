<?php

namespace OpenIDConnect\Contracts;

use Psr\Http\Message\ResponseInterface;

interface ResponseBuilder
{
    public function build(array $parameters): ResponseInterface;
}
