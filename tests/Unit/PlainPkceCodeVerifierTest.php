<?php

namespace Tests\Unit;

use InvalidArgumentException;
use OpenIDConnect\AbstractPkceCodeVerifier;
use OpenIDConnect\PlainPkceCodeVerifier;
use OpenIDConnect\S256PkceCodeVerifier;
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

    #[Test]
    public function shouldCreateVerifierFromMethodAndValue(): void
    {
        $value = 'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk';

        $s256 = AbstractPkceCodeVerifier::fromMethod('S256', $value);
        $plain = AbstractPkceCodeVerifier::fromMethod('plain', $value);

        $this->assertInstanceOf(S256PkceCodeVerifier::class, $s256);
        $this->assertInstanceOf(PlainPkceCodeVerifier::class, $plain);
        $this->assertSame($value, $s256->value());
        $this->assertSame($value, $plain->value());
    }

    #[Test]
    public function shouldThrowExceptionWhenMethodIsUnknown(): void
    {
        $this->expectException(InvalidArgumentException::class);

        AbstractPkceCodeVerifier::fromMethod('s256', 'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk');
    }
}
