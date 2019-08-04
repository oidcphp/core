<?php

namespace Tests\Core;

use OpenIDConnect\Client;
use OpenIDConnect\ClientMetadata;
use OpenIDConnect\ProviderMetadata;
use Tests\TestCase;

class ClientTest extends TestCase
{
    /**
     * @var Client
     */
    private $target;

    protected function setUp(): void
    {
        $this->target = new Client(
            ProviderMetadata::create([
                'issuer' => 'https://somewhere',
                'authorization_endpoint' => 'https://somewhere/auth',
                'token_endpoint' => 'https://somewhere/token',
                'jwks_uri' => 'https://somewhere/certs',
                'response_types_supported' => ['code'],
                'subject_types_supported' => ['public'],
                'id_token_signing_alg_values_supported' => ['RS256'],
            ]),
            ClientMetadata::create([
                'client_id' => 'some_id',
                'client_secret' => 'some_secret',
            ])
        );
    }

    protected function tearDown(): void
    {
        $this->target = null;
    }

    /**
     * @test
     */
    public function shouldReturnAuthorizationUrlWhenCallSame(): void
    {
        $expected = 'https://somewhere/auth?client_id=some_id&scope=openid&response_type=code';

        $actual = $this->target->authorizationUrl();

        $this->assertSame($expected, (string)$actual);
    }

    /**
     * @test
     */
    public function shouldReturnAuthorizationResponseWhenCallSame(): void
    {
        $expected = 'https://somewhere/auth?client_id=some_id&scope=openid&response_type=code';

        $actual = $this->target->authorizationResponse();

        $this->assertSame(302, $actual->getStatusCode());
        $this->assertSame($expected, (string)$actual->getHeaderLine('Location'));
    }
}
