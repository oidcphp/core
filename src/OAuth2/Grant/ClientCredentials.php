<?php

declare(strict_types=1);

namespace OpenIDConnect\OAuth2\Grant;

/**
 * Client credentials grant
 *
 * @link http://tools.ietf.org/html/rfc6749#section-1.3.4
 */
class ClientCredentials extends GrantType
{
    /**
     * @var string
     */
    protected $grantType = 'client_credentials';
}
