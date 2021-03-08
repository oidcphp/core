<?php

declare(strict_types=1);

namespace OpenIDConnect\Jwt\Checkers;

use Jose\Component\Checker\ClaimChecker;
use RuntimeException;

/**
 * @see https://openid.net/specs/openid-connect-backchannel-1_0.html#Validation
 */
class BackChannelLogoutEventsChecker implements ClaimChecker
{
    private const CLAIM_NAME = 'events';

    public function checkClaim($value): void
    {
        if (!array_key_exists('http://schemas.openid.net/event/backchannel-logout', $value)) {
            throw new RuntimeException('Bad events, http://schemas.openid.net/event/backchannel-logout not found.');
        }
    }

    public function supportedClaim(): string
    {
        return self::CLAIM_NAME;
    }
}
