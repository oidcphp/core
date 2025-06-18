<?php

declare(strict_types=1);

namespace OpenIDConnect\Jwt\Checkers;

use Jose\Component\Checker\ClaimChecker;
use Jose\Component\Checker\InvalidClaimException;

class NonceChecker implements ClaimChecker
{
    private const CLAIM_NAME = 'nonce';

    /**
     * @var string|null
     */
    private $nonce;

    /**
     * @param string|null $nonce
     */
    public function __construct(?string $nonce = null)
    {
        $this->nonce = $nonce;
    }

    public function checkClaim($value): void
    {
        if ($this->nonce !== null && $value !== $this->nonce) {
            throw new InvalidClaimException('Nonce check error', self::CLAIM_NAME, $value);
        }
    }

    public function supportedClaim(): string
    {
        return self::CLAIM_NAME;
    }
}
