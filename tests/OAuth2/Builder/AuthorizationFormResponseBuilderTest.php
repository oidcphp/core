<?php

namespace Tests\OAuth2\Builder;

use OpenIDConnect\OAuth2\Builder\AuthorizationFormResponseBuilder;
use OpenIDConnect\OAuth2\Exceptions\OAuth2ServerException;
use Tests\TestCase;

class AuthorizationFormResponseBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnAuthorizationHtmlWhenCallBuild(): void
    {
        $target = new AuthorizationFormResponseBuilder($this->createContainer());

        $actual = (string)$target->setProviderMetadata($this->createProviderMetadata())
            ->setClientInformation($this->createClientInformation())
            ->build([
                'foo' => 'a',
                'bar' => 'b',
            ])
            ->getBody();

        $this->assertStringContainsString('action="https://somewhere/auth"', $actual);
        $this->assertStringContainsString('name="foo" value="a"', $actual);
        $this->assertStringContainsString('name="bar" value="b"', $actual);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenCallBuildWithoutAuthorizationEndpoint(): void
    {
        $this->expectException(OAuth2ServerException::class);

        $target = new AuthorizationFormResponseBuilder($this->createContainer());

        $target->setProviderMetadata($this->createProviderMetadata([
            'authorization_endpoint' => null,
        ]))
            ->setClientInformation($this->createClientInformation())
            ->build([
                'foo' => 'a',
                'bar' => 'b',
            ]);
    }
}
