<?php

namespace OpenIDConnect\Core\Token;

use OpenIDConnect\OAuth2\Token\TokenFactoryInterface;
use OpenIDConnect\OAuth2\Token\TokenSetInterface;

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
