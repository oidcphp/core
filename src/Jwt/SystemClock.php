<?php

declare(strict_types=1);

namespace OpenIDConnect\Jwt;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

/**
 * The system clock given to the time based claim checkers of web-token/jwt-library.
 *
 * @internal
 * @link https://www.php-fig.org/psr/psr-20/
 */
final class SystemClock implements ClockInterface
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
