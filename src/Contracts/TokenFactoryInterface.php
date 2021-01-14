<?php

declare(strict_types=1);

namespace OpenIDConnect\Contracts;

interface TokenFactoryInterface
{
    /**
     * Create TokenSet by response from token endpoint
     *
     * @param array $parameters
     * @param int $clockTolerance
     * @return TokenSetInterface
     */
    public function create(array $parameters, $clockTolerance = 10): TokenSetInterface;
}
