<?php

namespace Tests\Unit;

use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\JsonConverter;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Serializer\CompactSerializer;
use MilesChou\Mocker\Psr18\MockClient;
use OpenIDConnect\Client;
use OpenIDConnect\Core\Claims;
use OpenIDConnect\Core\Exceptions\RelyingPartyException;
use OpenIDConnect\Jwt\JwtFactory;
use Psr\Http\Client\ClientInterface;
use Tests\TestCase;

class ClientHandleOpenIDConnectCallbackTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnTokenSetWhenEverythingOkay(): void
    {
        $providerMetadata = $this->createProviderMetadata();

        $clientInformation = $this->createClientInformation([
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

        $factory = new JwtFactory($providerMetadata, $clientInformation);

        $jws = $factory->createJwsBuilder()
            ->withPayload(JsonConverter::encode($payload))
            ->addSignature(new JWK($providerMetadata->jwkSet()->get(0)), ['alg' => 'RS256'])
            ->build();

        $expectedIdToken = (new CompactSerializer())->serialize($jws);

        /** @var Client $target */
        $target = new Client(
            $providerMetadata,
            $clientInformation,
            $this->createContainer([
                ClientInterface::class => (new MockClient())->appendResponseWithJson(
                    $this->createFakeTokenSetParameter(['id_token' => $expectedIdToken])
                ),
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

        $clientInformation = $this->createClientInformation([
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

        $factory = new JwtFactory($providerMetadata, $clientInformation);

        $jws = $factory->createJwsBuilder()
            ->withPayload(JsonConverter::encode($payload))
            ->addSignature(JWKFactory::createFromValues($providerMetadata->jwkSet()->get(0)), ['alg' => 'RS256'])
            ->build();

        /** @var Client $target */
        $target = new Client(
            $providerMetadata,
            $clientInformation,
            $this->createContainer([
                ClientInterface::class => (new MockClient())->appendResponseWithJson(
                    $this->createFakeTokenSetParameter(['id_token' => (new CompactSerializer())->serialize($jws)])
                ),
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
