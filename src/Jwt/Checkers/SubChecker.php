<?php

declare(strict_types=1);

namespace OpenIDConnect\Jwt\Checkers;

use Jose\Component\Checker\ClaimChecker;

class SubChecker implements ClaimChecker
{
    public function checkClaim($value): void
    {
        // @TODO
    }

    public function supportedClaim(): string
    {
        return 'sub';
    }
}
