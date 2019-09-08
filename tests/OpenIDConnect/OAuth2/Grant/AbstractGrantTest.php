<?php

namespace Tests\OpenIDConnect\OAuth2\Grant;

use OpenIDConnect\OAuth2\Grant\AbstractGrant;
use OpenIDConnect\OAuth2\Grant\AuthorizationCode;
use OpenIDConnect\OAuth2\Grant\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractGrantTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnStringWhenCastToString(): void
    {
        /** @var AbstractGrant|MockObject $target */
        $target = $this->getMockForAbstractClass(AbstractGrant::class);
        $target->method('getName')
            ->willReturn('whatever');

        $this->assertSame('whatever', (string)$target);
    }
}
