<?php

namespace Tests\OAuth2\Token;

use OpenIDConnect\OAuth2\Token\TokenFactory;
use Tests\TestCase;

class TokenFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeOkayWhenCreateByFactory(): void
    {
        $target = (new TokenFactory())->create([
            'access_token' => 'some-access-token',
            'custom' => 'whatever',
            'expires_in' => 3600,
            'refresh_token' => 'some-refresh-token',
            'scope' => 'some-scope',
        ]);

        $this->assertSame('some-access-token', $target->accessToken());
    }
}
