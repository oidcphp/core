<?php

namespace Tests\Unit\Http\Response;

use MilesChou\Mocker\Psr18\MockClient;
use MilesChou\Psr\Http\Message\HttpFactory;
use OpenIDConnect\Http\Response\AuthorizationRedirectResponseBuilder;
use Tests\TestCase;

class AuthorizationRedirectResponseBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnRedirectResponseWhenCallBuild(): void
    {
        $target = new AuthorizationRedirectResponseBuilder($this->createConfig(), new MockClient(), new HttpFactory());

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
