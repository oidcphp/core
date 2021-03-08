<?php

namespace Tests\Unit;

use InvalidArgumentException;
use OpenIDConnect\Claims;
use Tests\TestCase;

class ClaimsTest extends TestCase
{
    public function invalidToken(): iterable
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
}
