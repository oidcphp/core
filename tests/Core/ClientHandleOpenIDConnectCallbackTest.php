<?php

namespace Tests\Core;

use OpenIDConnect\Client;
use OpenIDConnect\Metadata\ClientMetadata;
use OpenIDConnect\Metadata\ProviderMetadata;
use Tests\TestCase;

class ClientHandleOpenIDConnectCallbackTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->singleton(ProviderMetadata::class, function () {
            return new ProviderMetadata($this->createProviderMetadataConfig());
        });

        $this->app->singleton(ClientMetadata::class, function () {
            return new ClientMetadata($this->createClientMetadataConfig());
        });
    }

    /**
     * @test
     */
    public function shouldReturnTokenSetWhenEverythingOkay(): void
    {
        /** @var Client $target */
        $target = new Client(
            $this->app->make(ProviderMetadata::class),
            $this->app->make(ClientMetadata::class),
            [
                'httpClient' => $this->createHttpClient([
                    $this->createFakeTokenEndpointResponse(['id_token' => 'whatever']),
                ]),
            ]
        );

        $actual = $target->handleOpenIDConnectCallback([
            'code' => 'some-code',
        ]);

        $this->assertSame('some-access-token', $actual->accessToken());
        $this->assertSame(3600, $actual->expiresIn());
        $this->assertSame('some-refresh-token', $actual->refreshToken());
        $this->assertSame(['some-scope'], $actual->scope());

        $this->assertSame('whatever', $actual->idTokenRaw());
    }
}
