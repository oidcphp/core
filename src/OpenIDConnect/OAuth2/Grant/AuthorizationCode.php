<?php

namespace OpenIDConnect\OAuth2\Grant;

/**
 * Authorization code grant.
 *
 * @see http://tools.ietf.org/html/rfc6749#section-1.3.1
 */
class AuthorizationCode extends AbstractGrant
{
    protected function getName(): string
    {
        return 'authorization_code';
    }

    protected function getRequiredRequestParameters(): array
    {
        return [
            'code',
        ];
    }
}
