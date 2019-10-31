<?php

declare(strict_types=1);

namespace OpenIDConnect\Core\Exceptions;

use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

class EntryNotFoundException extends RuntimeException implements NotFoundExceptionInterface
{
}
