<?php

namespace Tests\OpenIDConnect\OAuth2\Grant;

use OpenIDConnect\Exceptions\RelyingPartyException;
use OpenIDConnect\OAuth2\Grant\AuthorizationCode;
use OpenIDConnect\OAuth2\Grant\Factory;
use PHPUnit\Framework\TestCase;

class AuthorizationCodeTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnAuthorizationCodeWhenRegisterInFactory(): void
    {
        $factory = new Factory();
        $factory->setGrant('some', new AuthorizationCode());

        $this->assertInstanceOf(AuthorizationCode::class, $factory->getGrant('some'));
    }

    /**
     * @test
     */
    public function shouldReturnAuthorizationCodeWhenNotRegisterInFactory(): void
    {
        $factory = new Factory();

        $this->assertInstanceOf(AuthorizationCode::class, $factory->getGrant('authorization_code'));
    }

    /**
     * @test
     */
    public function shouldReturnOkayWhenRequireParametersIsReady(): void
    {
        $target = (new Factory())->getGrant('authorization_code');

        $actual = $target->prepareRequestParameters([
            'code' => 'some',
        ]);

        $this->assertSame('authorization_code', $actual['grant_type']);
        $this->assertSame('some', $actual['code']);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenRequireParametersIsNotReady(): void
    {
        $this->expectException(RelyingPartyException::class);

        $target = (new Factory())->getGrant('authorization_code');

        $target->prepareRequestParameters([]);
    }
}
