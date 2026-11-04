<?php

declare(strict_types=1);

namespace OpenIDConnect\OAuth2\Grant;

/**
 * Represents a refresh token grant.
 *
 * @link https://www.rfc-editor.org/rfc/rfc6749.html#section-6
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
