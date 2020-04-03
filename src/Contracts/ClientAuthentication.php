<?php

declare(strict_types=1);

namespace OpenIDConnect\Contracts;

use Psr\Http\Message\RequestInterface;

interface ClientAuthentication
{
    /**
     * Process authentication message in the request
     *
     * @param RequestInterface $request
     * @return RequestInterface
     */
    public function processRequest(RequestInterface $request): RequestInterface;
}
