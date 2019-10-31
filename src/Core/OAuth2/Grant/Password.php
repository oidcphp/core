<?php

namespace OpenIDConnect\Core\OAuth2\Grant;

/**
 * Resource owner password credentials grant
 *
 * @see http://tools.ietf.org/html/rfc6749#section-1.3.3
 */
class Password extends AbstractGrant
{
    protected function getName(): string
    {
        return 'password';
    }

    protected function getRequiredRequestParameters(): array
    {
        return [
            'username',
            'password',
        ];
    }
}
