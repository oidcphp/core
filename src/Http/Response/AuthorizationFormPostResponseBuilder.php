<?php

declare(strict_types=1);

namespace OpenIDConnect\Http\Response;

class AuthorizationFormPostResponseBuilder extends ConfigurableResponseBuilder
{
    /**
     * @var string
     */
    protected $method = self::METHOD_FORM;

    /**
     * @var string
     */
    protected $key = 'authorization_endpoint';
}
