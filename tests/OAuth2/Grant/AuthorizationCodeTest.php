<?php

namespace Tests\OAuth2\Grant;

use InvalidArgumentException;
use OpenIDConnect\OAuth2\Grant\AuthorizationCode;
use PHPUnit\Framework\TestCase;

class AuthorizationCodeTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnOkayWhenRequireParametersIsReady(): void
    {
        $target = new AuthorizationCode();

        $actual = $target->prepareTokenRequestParameters([
            'code' => 'some',
            'redirect_uri' => 'https://someredirect',
        ]);

        $this->assertSame('authorization_code', $actual['grant_type']);
        $this->assertSame('some', $actual['code']);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenParameterIsMissing(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $target = new AuthorizationCode();
        $target->prepareTokenRequestParameters([]);
    }
}
