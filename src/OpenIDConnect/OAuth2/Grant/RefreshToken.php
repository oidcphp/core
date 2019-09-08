<?php

namespace OpenIDConnect\OAuth2\Grant;

/**
 * Represents a refresh token grant.
 *
 * @see http://tools.ietf.org/html/rfc6749#section-6
 */
class RefreshToken extends AbstractGrant
{
    protected function getName(): string
    {
        return 'refresh_token';
    }

    protected function getRequiredRequestParameters(): array
    {
        return [
            'refresh_token',
        ];
    }
}
