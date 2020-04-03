<?php

declare(strict_types=1);

namespace OpenIDConnect\Token;

use OpenIDConnect\Contracts\TokenFactoryInterface;
use OpenIDConnect\Contracts\TokenSetInterface;

class TokenFactory implements TokenFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function create(array $parameters): TokenSetInterface
    {
        return new TokenSet($parameters);
    }
}
