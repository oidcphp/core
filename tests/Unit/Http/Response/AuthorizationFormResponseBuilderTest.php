<?php

namespace Tests\Unit\Http\Response;

use MilesChou\Mocker\Psr18\MockClient;
use MilesChou\Psr\Http\Message\HttpFactory;
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
        $target = new AuthorizationFormResponseBuilder(new MockClient(), new HttpFactory());

        $actual = (string)$target->setProviderMetadata($this->createProviderMetadata())
            ->setClientMetadata($this->createClientMetadata())
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

        $target = new \OpenIDConnect\Http\Response\AuthorizationFormResponseBuilder(new MockClient(), new HttpFactory());

        $target->setProviderMetadata($this->createProviderMetadata([
            'authorization_endpoint' => null,
        ]))
            ->setClientMetadata($this->createClientMetadata())
            ->build([
                'foo' => 'a',
                'bar' => 'b',
            ]);
    }
}
