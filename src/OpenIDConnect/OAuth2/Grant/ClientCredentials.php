<?php
/**
 * This file is part of the league/oauth2-client library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Alex Bilbie <hello@alexbilbie.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @link http://thephpleague.com/oauth2-client/ Documentation
 * @link https://packagist.org/packages/league/oauth2-client Packagist
 * @link https://github.com/thephpleague/oauth2-client GitHub
 */

namespace OpenIDConnect\OAuth2\Grant;

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
