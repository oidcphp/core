<?php

namespace Tests\Core\Token;

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
    }
}
