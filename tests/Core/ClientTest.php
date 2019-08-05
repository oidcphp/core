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

        $this->assertStringContainsStringIgnoringCase('response_type=code', $actual);
        $this->assertStringContainsStringIgnoringCase('client_id=some_id', $actual);
    }
}
