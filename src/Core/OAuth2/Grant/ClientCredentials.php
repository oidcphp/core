<?php

namespace OpenIDConnect\Core\OAuth2\Grant;

/**
 * Client credentials grant
 *
 * @see http://tools.ietf.org/html/rfc6749#section-1.3.4
 */
class ClientCredentials extends AbstractGrant
{
    protected function getName(): string
    {
        return 'client_credentials';
    }

    protected function getRequiredRequestParameters(): array
    {
        return [];
    }
}
