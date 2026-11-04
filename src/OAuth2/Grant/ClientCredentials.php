<?php

declare(strict_types=1);

namespace OpenIDConnect\OAuth2\Grant;

/**
 * Client credentials grant
 *
 * @link https://www.rfc-editor.org/rfc/rfc6749.html#section-1.3.4
 */
class ClientCredentials extends GrantType
{
    /**
     * @var string
     */
    protected $grantType = 'client_credentials';
}
