<?php

namespace Tests\Unit\Http\Request;

use MilesChou\Psr\Http\Client\Testing\MockClient;
use MilesChou\Psr\Http\Message\Testing\TestRequest;
use OpenIDConnect\Http\Request\TokenRequestBuilder;
use OpenIDConnect\OAuth2\Grant\AuthorizationCode;
use Tests\TestCase;

class TokenRequestBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnCorrectRequestInstance(): void
    {
        $target = new TokenRequestBuilder($this->createConfigWithClientMetadata([
            'client_id' => 'some_id',
            'client_secret' => 'some_secret',
        ]), new MockClient());

        $actual = $target->build([
            'code' => 'some-code',
            'redirect_uri' => 'some-redirect-uri',
        ], new AuthorizationCode());

        TestRequest::fromBaseRequest($actual)
            ->assertUri('https://somewhere/token')
            ->assertBodyContains('grant_type=authorization_code')
            ->assertBodyContains('code=some-code')
            ->assertBodyContains('redirect_uri=some-redirect-uri')
            ->assertBasicAuthentication('some_id', 'some_secret')
            ->assertHeader('content-type', 'application/x-www-form-urlencoded');
    }
}
