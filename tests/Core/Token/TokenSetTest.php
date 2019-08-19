<?php

namespace Tests\Core\Token;

use InvalidArgumentException;
use OpenIDConnect\Token\TokenSet;
use Tests\TestCase;

class TokenSetTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeOkayWhenDataIsOkay(): void
    {
        $target = new TokenSet($this->createFakeTokenSetParameter([
            'access_token' => 'some-access-token',
            'expires_in' => 3600,
            'id_token' => 'some-id-token',
            'refresh_token' => 'some-refresh-token',
            'scope' => 'some-scope',
            'addition' => 'some-addition',
        ]));

        $this->assertSame('some-access-token', $target->accessToken());
        $this->assertSame(3600, $target->expiresIn());
        $this->assertSame('some-id-token', $target->idToken());
        $this->assertSame('some-refresh-token', $target->refreshToken());
        $this->assertSame(['some-scope'], $target->scope());
        $this->assertSame('some-addition', $target->values('addition'));

        $this->assertTrue($target->hasExpiresIn());
        $this->assertTrue($target->hasIdToken());
        $this->assertTrue($target->hasRefreshToken());
        $this->assertTrue($target->hasScope());
        $this->assertTrue($target->has('addition'));

        $this->assertFalse($target->has('whatever'));
    }

    public function defaultKeys()
    {
        return array_map(function ($key) {
            return [$key];
        }, TokenSet::DEFAULT_KEYS);
    }

    /**
     * @dataProvider defaultKeys
     * @test
     */
    public function shouldThrowWhenCallValueWithDefaultKeys($key): void
    {
        $this->expectException(InvalidArgumentException::class);

        $target = new TokenSet($this->createFakeTokenSetParameter());

        $target->values($key);
    }
}
