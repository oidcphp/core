<?php

namespace Tests\OAuth2\Builder;

use OpenIDConnect\OAuth2\Builder\AuthorizationRedirectResponseBuilder;
use Tests\TestCase;

class AuthorizationRedirectResponseBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnRedirectResponseWhenCallBuild(): void
    {
        $target = new AuthorizationRedirectResponseBuilder($this->createContainer());

        $actual = $target->setProviderMetadata($this->createProviderMetadata())
            ->setClientInformation($this->createClientInformation())
            ->build([
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
