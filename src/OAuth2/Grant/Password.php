<?php

declare(strict_types=1);

namespace OpenIDConnect\OAuth2\Grant;

/**
 * Resource owner password credentials grant
 *
 * @see http://tools.ietf.org/html/rfc6749#section-1.3.3
 */
class Password extends GrantType
{
    /**
     * @var string
     */
    protected $grantType = 'password';

    /**
     * @var array<string>
     */
    protected $requiredParameters = [
        'username',
        'password',
    ];
}
