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
            return ProviderMetadata::create($this->createProviderMetadataConfig());
        });

        $this->app->singleton(ClientMetadata::class, function () {
            return ClientMetadata::create($this->createClientMetadataConfig());
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

        $this->assertStringStartsWith('https://somewhere/auth', $actual);
        $this->assertStringContainsStringIgnoringCase('state=', $actual);
        $this->assertStringContainsStringIgnoringCase('response_type=code', $actual);
        $this->assertStringContainsStringIgnoringCase('redirect_uri=', $actual);
        $this->assertStringContainsStringIgnoringCase('client_id=some_id', $actual);
    }

    /**
     * @test
     */
    public function shouldReturnAuthorizationPostFormWhenCallSame(): void
    {
        $actual = $this->target->authorizationPost();

        $this->assertStringContainsStringIgnoringCase('<form method="post" action="https://somewhere/auth">', $actual);
        $this->assertStringContainsStringIgnoringCase('name="state"', $actual);
        $this->assertStringContainsStringIgnoringCase('name="response_type" value="code"', $actual);
        $this->assertStringContainsStringIgnoringCase('name="redirect_uri" value="https://someredirect"', $actual);
        $this->assertStringContainsStringIgnoringCase('name="client_id" value="some_id"', $actual);
    }
}
