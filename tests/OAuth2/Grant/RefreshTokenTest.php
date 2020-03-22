<?php

namespace Tests\OAuth2\Grant;

use OpenIDConnect\OAuth2\Grant\RefreshToken;
use PHPUnit\Framework\TestCase;

class RefreshTokenTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnOkayWhenRequireParametersIsReady(): void
    {
        $target = new RefreshToken();

        $actual = $target->prepareTokenRequestParameters([
            'refresh_token' => 'some',
        ]);

        $this->assertSame('refresh_token', $actual['grant_type']);
        $this->assertSame('some', $actual['refresh_token']);
    }
}
