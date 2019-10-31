<?php

namespace Tests\Core;

use GuzzleHttp\ClientInterface;
use Jose\Component\Core\Util\JsonConverter;
use Jose\Component\Signature\Serializer\CompactSerializer;
use OpenIDConnect\Core\Builder;
use OpenIDConnect\Core\Claims;
use OpenIDConnect\Core\Client;
use OpenIDConnect\Core\Exceptions\RelyingPartyException;
use Tests\TestCase;

class ClientHandleOpenIDConnectCallbackTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnTokenSetWhenEverythingOkay(): void
    {
        $providerMetadata = $this->createProviderMetadata();

        $clientMetadata = $this->createClientRegistration([
            'client_id' => 'some-aud',
        ]);

        $payload = [
            'aud' => 'some-aud',
            'exp' => time() + 3600,
            'iat' => time(),
            'iss' => 'some-iss',
            'nonce' => '0123456789',
            'sub' => 'some-sub',
        ];

        $jws = $providerMetadata->createJwtFactory($clientMetadata)->createJwsBuilder()
            ->withPayload(JsonConverter::encode($payload))
            ->addSignature($providerMetadata->jwkSet()->get(0), ['alg' => 'RS256'])
            ->build();

        $expectedIdToken = (new CompactSerializer())->serialize($jws);

        /** @var Client $target */
        $target = new Client(
            $providerMetadata,
            $clientMetadata,
            Builder::createDefaultContainer([
                ClientInterface::class => $this->createHttpClient([
                    $this->createFakeTokenEndpointResponse([
                        'id_token' => $expectedIdToken,
                    ]),
                ]),
            ])
        );

        $actual = $target->handleOpenIDConnectCallback([
            'code' => 'some-code',
        ], [
            'redirect_uri' => 'https://someredirect',
            'nonce' => '0123456789',
        ]);

        $this->assertSame('some-access-token', $actual->accessToken());
        $this->assertSame(3600, $actual->expiresIn());
        $this->assertSame('some-refresh-token', $actual->refreshToken());
        $this->assertSame(['some-scope'], $actual->scope());
        $this->assertSame($expectedIdToken, $actual->idToken());

        $this->assertInstanceOf(Claims::class, $actual->idTokenClaims());
    }

    /**
     * @test
     */
    public function shouldReturnTokenSetWhenNonceIsNotMatch(): void
    {
        $this->expectException(RelyingPartyException::class);

        $providerMetadata = $this->createProviderMetadata();

        $clientMetadata = $this->createClientRegistration([
            'client_id' => 'some-aud',
        ]);

        $payload = [
            'aud' => 'some-aud',
            'exp' => time() + 3600,
            'iat' => time(),
            'iss' => 'some-iss',
            'nonce' => '0123456789',
            'sub' => 'some-sub',
        ];

        $jws = $providerMetadata->createJwtFactory($clientMetadata)->createJwsBuilder()
            ->withPayload(JsonConverter::encode($payload))
            ->addSignature($providerMetadata->jwkSet()->get(0), ['alg' => 'RS256'])
            ->build();

        /** @var Client $target */
        $target = new Client(
            $providerMetadata,
            $clientMetadata,
            Builder::createDefaultContainer([
                ClientInterface::class => $this->createHttpClient([
                    $this->createFakeTokenEndpointResponse([
                        'id_token' => (new CompactSerializer())->serialize($jws),
                    ]),
                ]),
            ])
        );

        $actual = $target->handleOpenIDConnectCallback([
            'code' => 'some-code',
        ], [
            'redirect_uri' => 'https://someredirect',
            'nonce' => 'something-another',
        ]);

        $actual->idTokenClaims();
    }
}
