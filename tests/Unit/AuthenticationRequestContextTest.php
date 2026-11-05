<?php

namespace Tests\Unit;

use OpenIDConnect\AuthenticationRequestContext;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use TypeError;

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

    #[Test]
    public function shouldBeRestoredFromArrayWhichWasStoredAsJson(): void
    {
        $expected = new AuthenticationRequestContext('https://someredirect', 'some-state', 'some-nonce');

        $actual = AuthenticationRequestContext::fromArray(json_decode(json_encode($expected->toArray()), true));

        $this->assertEquals($expected, $actual);
    }

    #[Test]
    #[DataProvider('invalidArray')]
    public function shouldThrowTypeErrorWhenRestoreFromArrayWithMissingKeyOrNotString(array $data): void
    {
        $this->expectException(TypeError::class);

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
            'not string' => [array_merge($valid, ['state' => ['a']])],
        ];
    }
}
