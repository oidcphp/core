<?php

namespace Tests\OAuth2\Grant;

use OpenIDConnect\OAuth2\Grant\Password;
use PHPUnit\Framework\TestCase;

class PasswordTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnOkayWhenRequireParametersIsReady(): void
    {
        $target = new Password();

        $actual = $target->prepareTokenRequestParameters([
            'username' => 'some-username',
            'password' => 'some-password',
        ]);

        $this->assertSame('password', $actual['grant_type']);
        $this->assertSame('some-username', $actual['username']);
        $this->assertSame('some-password', $actual['password']);
    }
}
