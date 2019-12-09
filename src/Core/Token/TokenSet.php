<?php

namespace OpenIDConnect\Core\Token;

use OpenIDConnect\Core\Token\Concerns\IdToken;
use OpenIDConnect\OAuth2\Token\TokenSet as BaseTokenSet;

class TokenSet extends BaseTokenSet implements TokenSetInterface
{
    use IdToken;

    /**
     * {@inheritDoc}
     */
    public function idToken(): ?string
    {
        return $this->get('id_token');
    }
}
