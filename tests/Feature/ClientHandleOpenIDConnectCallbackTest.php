<?php

namespace Tests\Feature;

use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\JsonConverter;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Serializer\CompactSerializer;
use MilesChou\Psr\Http\Client\Testing\MockClient;
use OpenIDConnect\Client;
use OpenIDConnect\Config;
use OpenIDConnect\Exceptions\RelyingPartyException;
use OpenIDConnect\Jwt\JwtFactory;
use Tests\TestCase;

class ClientHandleOpenIDConnectCallbackTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnTokenSetWhenEverythingOkay(): void
    {
        $providerMetadata = $this->createProviderMetadata([
            'issuer' => 'some-iss',
        ]);

        $clientMetadata = $this->createClientMetadata([
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

        $factory = new JwtFactory(new Config($providerMetadata, $clientMetadata));

        $jws = $factory->createJwsBuilder()
            ->withPayload(JsonConverter::encode($payload))
            ->addSignature(new JWK($providerMetadata->jwkSet()->get(0)), ['alg' => 'RS256'])
            ->build();

        $expectedIdToken = (new CompactSerializer())->serialize($jws);

        $mockClient = (new MockClient())->appendResponseWithJson(
            $this->createFakeTokenSetParameter(['id_token' => $expectedIdToken])
        );

        $target = new Client(new Config($providerMetadata, $clientMetadata), $mockClient);

        $actual = $target->handleCallback([
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

        $claims = $actual->idTokenClaims();

        $this->assertSame('some-aud', $claims->aud());
        $this->assertSame('some-iss', $claims->iss());
        $this->assertSame('0123456789', $claims->nonce());
        $this->assertSame('some-sub', $claims->sub());
    }

    /**
     * @test
     */
    public function shouldReturnTokenSetWhenNonceIsNotMatch(): void
    {
        $this->expectException(RelyingPartyException::class);

        $providerMetadata = $this->createProviderMetadata();

        $clientMetadata = $this->createClientMetadata([
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

        $factory = new JwtFactory(new Config($providerMetadata, $clientMetadata));

        $jws = $factory->createJwsBuilder()
            ->withPayload(JsonConverter::encode($payload))
            ->addSignature(JWKFactory::createFromValues($providerMetadata->jwkSet()->get(0)), ['alg' => 'RS256'])
            ->build();

        $mockClient = (new MockClient())->appendResponseWithJson(
            $this->createFakeTokenSetParameter(['id_token' => (new CompactSerializer())->serialize($jws)])
        );

        $target = new Client(new Config($providerMetadata, $clientMetadata), $mockClient);

        $actual = $target->handleCallback([
            'code' => 'some-code',
        ], [
            'redirect_uri' => 'https://someredirect',
            'nonce' => 'something-another',
        ]);

        $actual->idTokenClaims();
    }
}
