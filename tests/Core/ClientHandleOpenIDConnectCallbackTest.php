<?php

namespace Tests\Core;

use OpenIDConnect\Client;
use Tests\TestCase;

class ClientHandleOpenIDConnectCallbackTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnTokenSetWhenEverythingOkay(): void
    {
        /** @var Client $target */
        $target = new Client(
            $this->createProviderMetadata(),
            $this->createClientMetadata(),
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

        $this->assertSame('whatever', $actual->idToken());
    }
}
