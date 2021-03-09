<?php

declare(strict_types=1);

namespace OpenIDConnect\Jwt\Checkers;

use Jose\Component\Checker\ClaimChecker;
use OpenIDConnect\Exceptions\OpenIDProviderException;

/**
 * Use for Logout token
 */
class NonceNotContainChecker implements ClaimChecker
{
    public function checkClaim($value): void
    {
        throw new OpenIDProviderException("Should not contain 'nonce' claim");
    }

    public function supportedClaim(): string
    {
        return 'nonce';
    }
}
