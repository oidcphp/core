<?php

namespace Tests\OAuth2\Token;

use DomainException;
use OpenIDConnect\OAuth2\Token\TokenSet;
use Tests\TestCase;

class TokenSetTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeOkayWhenAllInformationIsReady(): void
    {
        $target = new TokenSet([
            'access_token' => 'some-access-token',
            'custom' => 'whatever',
            'expires_in' => 3600,
            'refresh_token' => 'some-refresh-token',
            'scope' => 'some-scope',
        ]);

        $this->assertTrue($target->has('access_token'));
        $this->assertTrue($target->has('custom'));
        $this->assertTrue($target->has('expires_in'));
        $this->assertTrue($target->has('refresh_token'));
        $this->assertTrue($target->has('scope'));
        $this->assertFalse($target->has('not-exist'));

        $this->assertSame('some-access-token', $target->accessToken());
        $this->assertSame(3600, $target->expiresIn());
        $this->assertSame('some-refresh-token', $target->refreshToken());
        $this->assertSame(['some-scope'], $target->scope());

        $this->assertSame('whatever', $target->get('custom'));
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenRequireKeyIsMissing(): void
    {
        $this->expectException(DomainException::class);

        $target = new TokenSet([]);

        $target->accessToken();
    }

    /**
     * @test
     */
    public function shouldReturnNullWhenNoScope(): void
    {
        $target = new TokenSet([]);

        $this->assertNull($target->scope());
    }

    /**
     * @test
     */
    public function shouldReturnArrayDirectlyWhenScopeIsArray(): void
    {
        $target = new TokenSet([
            'scope' => ['a-b', 'c d'],
        ]);

        $this->assertSame(['a-b', 'c d'], $target->scope());
    }

    /**
     * @test
     */
    public function shouldSerializeToJsonByParameterArray(): void
    {
        $target = new TokenSet([
            'foo' => 'bar',
        ]);

        $this->assertSame('{"foo":"bar"}', json_encode($target));
    }
}
