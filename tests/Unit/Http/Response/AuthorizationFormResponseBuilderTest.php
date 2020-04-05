<?php

namespace Tests\Unit\Http\Response;

use MilesChou\Psr\Http\Client\Testing\MockClient;
use MilesChou\Psr\Http\Message\Testing\TestResponse;
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

        $actual = $target->build([
            'foo' => 'a',
            'bar' => 'b',
        ]);

        TestResponse::fromBaseResponse($actual)
            ->assertSee('action="https://somewhere/auth"')
            ->assertSee('name="foo" value="a"')
            ->assertSee('name="bar" value="b"');
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
