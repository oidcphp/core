<?php

namespace Tests\Unit;

use OpenIDConnect\AuthenticationRequestContext;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthenticationRequestContextTest extends TestCase
{
    #[Test]
    public function shouldMapValuesToProperties(): void
    {
        $target = new AuthenticationRequestContext('https://someredirect', 'some-state', 'some-nonce');

        $this->assertSame('https://someredirect', $target->redirectUri);
        $this->assertSame('some-state', $target->state);
        $this->assertSame('some-nonce', $target->nonce);
    }
}
