<?php

namespace Tests\Unit;

use InvalidArgumentException;
use OpenIDConnect\PlainPkceCodeVerifier;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PlainPkceCodeVerifierTest extends TestCase
{
    #[Test]
    public function shouldReturnVerifierItselfAsChallenge(): void
    {
        $target = new PlainPkceCodeVerifier('dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk');

        $this->assertSame('dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk', $target->challenge());
        $this->assertSame('plain', $target->challengeMethod());
    }

    #[Test]
    public function shouldGenerateRandomValueOf43Characters(): void
    {
        $actual = PlainPkceCodeVerifier::generate();

        $this->assertInstanceOf(PlainPkceCodeVerifier::class, $actual);
        $this->assertSame(43, strlen($actual->value()));
    }

    #[Test]
    public function shouldThrowExceptionWhenValueIsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PlainPkceCodeVerifier(str_repeat('a', 42));
    }
}
