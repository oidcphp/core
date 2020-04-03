<?php

declare(strict_types=1);

namespace OpenIDConnect\Exceptions;

use OpenIDConnect\Contracts\OAuth2Exception;

class OAuth2ClientException extends \RuntimeException implements OAuth2Exception
{
}
