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
        parent::setUp();

        $this->app->singleton(ProviderMetadata::class, function () {
            return ProviderMetadata::create([
                'issuer' => 'https://somewhere',
                'authorization_endpoint' => 'https://somewhere/auth',
                'token_endpoint' => 'https://somewhere/token',
                'jwks_uri' => 'https://somewhere/certs',
                'response_types_supported' => ['code'],
                'subject_types_supported' => ['public'],
                'id_token_signing_alg_values_supported' => ['RS256'],
            ]);
        });

        $this->app->singleton(ClientMetadata::class, function () {
            return ClientMetadata::create([
                'client_id' => 'some_id',
                'client_secret' => 'some_secret',
                'redirect_uri' => 'https://someredirect',
            ]);
        });

        $this->target = $this->app->make(Client::class);
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
        $actual = $this->target->authorizationUrl();

        $this->assertContains('response_type=code', $actual);
        $this->assertContains('client_id=some_id', $actual);
    }
}
