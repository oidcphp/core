<?php

namespace Tests\OpenIDConnect\OAuth2\Grant;

use OpenIDConnect\Exceptions\RelyingPartyException;
use OpenIDConnect\OAuth2\Grant\Factory;
use OpenIDConnect\OAuth2\Grant\RefreshToken;
use PHPUnit\Framework\TestCase;

class RefreshTokenTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnRefreshTokenWhenRegisterInFactory(): void
    {
        $factory = new Factory();
        $factory->setGrant('some', new RefreshToken());

        $this->assertInstanceOf(RefreshToken::class, $factory->getGrant('some'));
    }

    /**
     * @test
     */
    public function shouldReturnRefreshTokenWhenNotRegisterInFactory(): void
    {
        $factory = new Factory();

        $this->assertInstanceOf(RefreshToken::class, $factory->getGrant('refresh_token'));
    }

    /**
     * @test
     */
    public function shouldReturnOkayWhenRequireParametersIsReady(): void
    {
        $target = (new Factory())->getGrant('refresh_token');

        $actual = $target->prepareRequestParameters([
            'refresh_token' => 'some',
        ]);

        $this->assertSame('refresh_token', $actual['grant_type']);
        $this->assertSame('some', $actual['refresh_token']);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenRequireParametersIsNotReady(): void
    {
        $this->expectException(RelyingPartyException::class);

        $target = (new Factory())->getGrant('refresh_token');

        $target->prepareRequestParameters([]);
    }
}
