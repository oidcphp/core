<?php

namespace Tests\Core\OAuth2\Grant;

use OpenIDConnect\Core\Exceptions\RelyingPartyException;
use OpenIDConnect\Core\OAuth2\Grant\GrantFactory;
use OpenIDConnect\Core\OAuth2\Grant\RefreshToken;
use PHPUnit\Framework\TestCase;

class RefreshTokenTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnRefreshTokenWhenRegisterInFactory(): void
    {
        $factory = new GrantFactory();
        $factory->setGrant('some', new RefreshToken());

        $this->assertInstanceOf(RefreshToken::class, $factory->getGrant('some'));
    }

    /**
     * @test
     */
    public function shouldReturnRefreshTokenWhenNotRegisterInFactory(): void
    {
        $factory = new GrantFactory();

        $this->assertInstanceOf(RefreshToken::class, $factory->getGrant('refresh_token'));
    }

    /**
     * @test
     */
    public function shouldReturnOkayWhenRequireParametersIsReady(): void
    {
        $target = (new GrantFactory())->getGrant('refresh_token');

        $actual = $target->prepareRequestParameters([
            'refresh_token' => 'some',
            'redirect_uri' => 'https://someredirect',
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

        $target = (new GrantFactory())->getGrant('refresh_token');

        $target->prepareRequestParameters([
            'redirect_uri' => 'https://someredirect',
        ]);
    }
}
