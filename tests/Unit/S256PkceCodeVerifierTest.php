<?php

namespace Tests\Unit;

use InvalidArgumentException;
use OpenIDConnect\S256PkceCodeVerifier;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class S256PkceCodeVerifierTest extends TestCase
{
    #[Test]
    public function shouldReturnS256ChallengeOfRfc7636AppendixB(): void
    {
        $target = new S256PkceCodeVerifier('dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk');

        $this->assertSame('E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM', $target->challenge());
        $this->assertSame('dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk', $target->value());
        $this->assertSame('dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk', (string)$target);
        $this->assertSame('S256', $target->challengeMethod());
    }

    #[Test]
    public function shouldGenerateRandomValueOf43Characters(): void
    {
        $a = S256PkceCodeVerifier::generate();
        $b = S256PkceCodeVerifier::generate();

        $this->assertMatchesRegularExpression('/^[A-Za-z0-9\-._~]{43}$/', $a->value());
        $this->assertNotSame($a->value(), $b->value());
    }

    #[Test]
    public function shouldAcceptBoundaryLength(): void
    {
        $this->assertSame(43, strlen((new S256PkceCodeVerifier(str_repeat('a', 43)))->value()));
        $this->assertSame(128, strlen((new S256PkceCodeVerifier(str_repeat('a', 128)))->value()));
    }

    #[Test]
    #[DataProvider('invalidValue')]
    public function shouldThrowExceptionWhenValueIsInvalid(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        new S256PkceCodeVerifier($value);
    }

    public static function invalidValue(): array
    {
        return [
            'too short' => [str_repeat('a', 42)],
            'too long' => [str_repeat('a', 129)],
            'invalid character' => [str_repeat('a', 42) . '+'],
            'trailing newline' => [str_repeat('a', 43) . "\n"],
            'empty' => [''],
        ];
    }
}
