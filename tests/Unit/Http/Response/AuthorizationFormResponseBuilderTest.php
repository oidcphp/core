<?php

namespace Tests\Unit\Http\Response;

use MilesChou\Psr\Http\Client\Testing\MockClient;
use OpenIDConnect\Exceptions\OAuth2ServerException;
use OpenIDConnect\Http\Response\AuthorizationFormResponseBuilder;
use Tests\TestCase;

class AuthorizationFormResponseBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnAuthorizationHtmlWhenCallBuild(): void
    {
        $target = new AuthorizationFormResponseBuilder($this->createConfig(), new MockClient());

        $actual = (string)$target->build([
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

        $target = new AuthorizationFormResponseBuilder($this->createConfig([
            'authorization_endpoint' => null,
        ]), new MockClient());

        $target->build([
            'foo' => 'a',
            'bar' => 'b',
        ]);
    }
}
