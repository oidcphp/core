<?php

declare(strict_types=1);

namespace OpenIDConnect\Exceptions;

use OpenIDConnect\Contracts\OAuth2Exception;

class OAuth2ServerException extends \RuntimeException implements OAuth2Exception
{
}
