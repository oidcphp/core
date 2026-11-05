<?php

namespace Tests\Unit\OAuth2\Grant;

use OpenIDConnect\OAuth2\Grant\RefreshToken;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RefreshTokenTest extends TestCase
{
    #[Test]
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
