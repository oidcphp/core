<?php

declare(strict_types=1);

namespace OpenIDConnect\OAuth2\Grant;

/**
 * Represents a refresh token grant.
 *
 * @see http://tools.ietf.org/html/rfc6749#section-6
 */
class RefreshToken extends GrantType
{
    /**
     * @var string
     */
    protected $grantType = 'refresh_token';

    /**
     * @var array<string>
     */
    protected $requiredParameters = [
        'refresh_token',
    ];
}
