<?php

declare(strict_types=1);

namespace OpenIDConnect\OAuth2\Token;

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
