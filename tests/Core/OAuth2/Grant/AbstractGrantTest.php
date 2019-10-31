<?php

namespace Tests\Core\OAuth2\Grant;

use OpenIDConnect\Core\OAuth2\Grant\AbstractGrant;
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
