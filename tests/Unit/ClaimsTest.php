<?php

namespace Tests\Unit;

use Base64Url\Base64Url;
use InvalidArgumentException;
use OpenIDConnect\Jwt\Claims;
use Tests\TestCase;

class ClaimsTest extends TestCase
{
    public static function invalidToken(): iterable
    {
        yield [''];
        yield ['0'];
        yield ['a.b'];
        yield ['a.b.c.d'];
    }

    /**
     * @test
     * @dataProvider invalidToken
     */
    public function shouldThrowInvalidArgumentExceptionWithUnsupportedType($invalidInput): void
    {
        $this->expectException(InvalidArgumentException::class);

        Claims::createFromJwsString($invalidInput);
    }

    /**
     * @test
     */
    public function shouldBeOkWhenBasicJwsType(): void
    {
        // W10 means [] with base64url encoded
        $actual = Claims::createFromJwsString('a.W10.b');

        $this->assertSame([], $actual->all());
    }

    /**
     * @test
     */
    public function shouldBeOkWhenBasicJwsTypeWithNoneAlg(): void
    {
        // W10 means [] with base64url encoded
        $actual = Claims::createFromJwsString('a.W10.');

        $this->assertSame([], $actual->all());
    }

    /**
     * @test
     */
    public function shouldBeOkWhenParseLogoutToken(): void
    {
        $payload = Base64Url::encode(json_encode([
            'aud' => 'some-aud',
            'iss' => 'some-iss',
            'iat' => 1615284779,
            'jti' => 'some-jti',
            'sid' => 'some-sid',
            'events' => [
                'http://schemas.openid.net/event/backchannel-logout' => [],
            ],
        ]));

        $token = 'eyJhbGciOiJSUzI1NiJ9.eyJhdWQiOiJzb21lLWF1ZCIsImlzcyI6InNvbWUtaXNzIiwiaWF0IjoxNjE1Mjg0Nzc5LCJqdGkiOiJzb21lLWp0aSIsInNpZCI6InNvbWUtc2lkIiwiZXZlbnRzIjp7Imh0dHA6XC9cL3NjaGVtYXMub3BlbmlkLm5ldFwvZXZlbnRcL2JhY2tjaGFubmVsLWxvZ291dCI6W119fQ.';

        $actual = Claims::createFromJwsString($token);

        $this->assertSame('some-aud', $actual->aud());
        $this->assertSame('some-iss', $actual->iss());
        $this->assertSame(1615284779, $actual->iat());
        $this->assertSame('some-jti', $actual->jti());
        $this->assertSame('some-sid', $actual->sid());
        $this->assertSame(['http://schemas.openid.net/event/backchannel-logout' => []], $actual->events());
    }
}
