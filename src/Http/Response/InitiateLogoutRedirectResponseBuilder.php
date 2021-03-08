<?php

declare(strict_types=1);

namespace OpenIDConnect\Http\Response;

/**
 * @see https://openid.net/specs/openid-connect-rpinitiated-1_0.html#RPLogout
 */
class InitiateLogoutRedirectResponseBuilder extends ConfigurableResponseBuilder
{
    /**
     * @var string
     */
    protected $method = self::METHOD_REDIRECT;

    /**
     * @var string
     */
    protected $key = 'end_session_endpoint';
}
