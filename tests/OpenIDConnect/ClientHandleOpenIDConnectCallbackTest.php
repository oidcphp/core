<?php

namespace Tests\OpenIDConnect;

use GuzzleHttp\ClientInterface;
use Jose\Component\Core\JWKSet;
use Jose\Component\Core\Util\JsonConverter;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Serializer\CompactSerializer;
use OpenIDConnect\Builder;
use OpenIDConnect\Claims;
use OpenIDConnect\Client;
use OpenIDConnect\Container\Container;
use OpenIDConnect\Exceptions\RelyingPartyException;
use OpenIDConnect\Metadata\ProviderMetadata;
use OpenIDConnect\OAuth2\Grant\GrantFactory;
use OpenIDConnect\Token\TokenSet;
use Tests\TestCase;

class ClientHandleOpenIDConnectCallbackTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnTokenSetWhenEverythingOkay(): void
    {
        $jwk = JWKFactory::createRSAKey(1024, ['alg' => 'RS256']);
        $jwks = JsonConverter::encode(new JWKSet([$jwk]));

        $providerMetadata = new ProviderMetadata($this->createProviderMetadataConfig(), JsonConverter::decode($jwks));

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
            ->addSignature($jwk, ['alg' => 'RS256'])
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

        $jwk = JWKFactory::createRSAKey(1024, ['alg' => 'RS256']);
        $jwks = JsonConverter::encode(new JWKSet([$jwk]));

        $providerMetadata = new ProviderMetadata($this->createProviderMetadataConfig(), JsonConverter::decode($jwks));

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
            ->addSignature($jwk, ['alg' => 'RS256'])
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
