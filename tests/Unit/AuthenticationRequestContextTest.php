<?php

namespace Tests\Unit;

use InvalidArgumentException;
use OpenIDConnect\AuthenticationRequestContext;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthenticationRequestContextTest extends TestCase
{
    #[Test]
    public function shouldGenerateDifferentRandomStateAndNonce(): void
    {
        $a = AuthenticationRequestContext::create('https://someredirect');
        $b = AuthenticationRequestContext::create('https://someredirect');

        $this->assertSame(32, strlen($a->state()));
        $this->assertSame(32, strlen($a->nonce()));
        $this->assertNotSame($a->state(), $a->nonce());
        $this->assertNotSame($a->state(), $b->state());
        $this->assertNotSame($a->nonce(), $b->nonce());
    }

    #[Test]
    public function shouldKeepGivenValues(): void
    {
        $target = AuthenticationRequestContext::create('https://someredirect', 'some-state', 'some-nonce');

        $this->assertSame('https://someredirect', $target->redirectUri());
        $this->assertSame('some-state', $target->state());
        $this->assertSame('some-nonce', $target->nonce());
    }

    #[Test]
    public function shouldBeRestoredFromArray(): void
    {
        $expected = AuthenticationRequestContext::create('https://someredirect');

        $actual = AuthenticationRequestContext::fromArray(json_decode(json_encode($expected->toArray()), true));

        $this->assertEquals($expected, $actual);
    }

    #[Test]
    public function shouldThrowExceptionWhenCreateWithEmptyValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        AuthenticationRequestContext::create('https://someredirect', '');
    }

    #[Test]
    #[DataProvider('invalidArray')]
    public function shouldThrowExceptionWhenRestoreFromInvalidArray(array $data): void
    {
        $this->expectException(InvalidArgumentException::class);

        AuthenticationRequestContext::fromArray($data);
    }

    public static function invalidArray(): array
    {
        $valid = ['redirect_uri' => 'https://someredirect', 'state' => 's', 'nonce' => 'n'];

        return [
            'empty' => [[]],
            'missing state' => [array_diff_key($valid, ['state' => 1])],
            'missing nonce' => [array_diff_key($valid, ['nonce' => 1])],
            'missing redirect_uri' => [array_diff_key($valid, ['redirect_uri' => 1])],
            'unknown key' => [$valid + ['scope' => 'openid']],
            'not string' => [array_merge($valid, ['state' => ['a']])],
            'empty string' => [array_merge($valid, ['nonce' => ''])],
        ];
    }
}
