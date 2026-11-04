<?php

declare(strict_types=1);

namespace OpenIDConnect\Http\Response;

/**
 * @link https://www.rfc-editor.org/rfc/rfc6749.html#section-4.1.1
 * @link https://www.rfc-editor.org/rfc/rfc6749.html#section-4.2.1
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
