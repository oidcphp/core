<?php

declare(strict_types=1);

namespace OpenIDConnect\OAuth2\Grant;

/**
 * Authorization code grant.
 *
 * @see http://tools.ietf.org/html/rfc6749#section-1.3.1
 */
class AuthorizationCode extends GrantType
{
    /**
     * @var string
     */
    protected $grantType = 'authorization_code';

    /**
     * @var array<string>
     */
    protected $requiredParameters = [
        'code',
        'redirect_uri',
    ];
}
