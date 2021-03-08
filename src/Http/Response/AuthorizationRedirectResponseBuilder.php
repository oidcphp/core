<?php

declare(strict_types=1);

namespace OpenIDConnect\Http\Response;

/**
 * @see https://tools.ietf.org/html/rfc6749#section-4.1.1
 * @see https://tools.ietf.org/html/rfc6749#section-4.2.1
 */
class AuthorizationRedirectResponseBuilder extends ConfigurableResponseBuilder
{
    /**
     * @var string
     */
    protected $method = self::METHOD_REDIRECT;

    /**
     * @var string
     */
    protected $key = 'authorization_endpoint';
}
