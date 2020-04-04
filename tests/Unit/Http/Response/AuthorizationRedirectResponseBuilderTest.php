<?php

namespace Tests\Unit\Http\Response;

use MilesChou\Psr\Http\Client\Testing\MockClient;
use OpenIDConnect\Http\Response\AuthorizationRedirectResponseBuilder;
use Tests\TestCase;

class AuthorizationRedirectResponseBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnRedirectResponseWhenCallBuild(): void
    {
        $target = new AuthorizationRedirectResponseBuilder($this->createConfig(), new MockClient());

        $actual = $target->build([
            'foo' => 'a',
            'bar' => 'b',
        ]);

        $this->assertSame(302, $actual->getStatusCode());

        $actualLocation = $actual->getHeaderLine('Location');

        $this->assertStringStartsWith('https://somewhere/auth', $actualLocation);
        $this->assertStringContainsString('foo=a', $actualLocation);
        $this->assertStringContainsString('bar=b', $actualLocation);
    }
}
