<?php

namespace Tests\Core\Token;

use Jose\Component\Core\JWKSet;
use Jose\Component\Core\Util\JsonConverter;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Serializer\CompactSerializer;
use OpenIDConnect\Core\Jwt\JwtFactory;
use OpenIDConnect\Core\Token\TokenSet;
use OpenIDConnect\Config\ProviderMetadata;
use Tests\TestCase;

class TokenSetTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeOkayWhenDataIsOkay(): void
    {
        $target = new TokenSet($this->createFakeTokenSetParameter([
            'access_token' => 'some-access-token',
            'expires_in' => 3600,
            'id_token' => 'some-id-token',
            'refresh_token' => 'some-refresh-token',
            'scope' => 'some-scope',
            'addition' => 'some-addition',
        ]), $this->createProviderMetadata(), $this->createClientInformation());

        $this->assertSame('some-access-token', $target->accessToken());
        $this->assertSame(3600, $target->expiresIn());
        $this->assertSame('some-id-token', $target->idToken());
        $this->assertSame('some-refresh-token', $target->refreshToken());
        $this->assertSame(['some-scope'], $target->scope());
        $this->assertSame('some-addition', $target->get('addition'));

        $this->assertTrue($target->has('addition'));
        $this->assertFalse($target->has('whatever'));
    }

    /**
     * @test
     */
    public function shouldBeOkayWhenUseAdditionJwk(): void
    {
        $jwk = JWKFactory::createRSAKey(1024, ['alg' => 'RS256']);
        $jwks = JsonConverter::encode(new JWKSet([$jwk]));

        $additionJwk = JWKFactory::createFromSecret('whatever', ['alg' => 'HS256']);

        $providerMetadata = new ProviderMetadata($this->createProviderMetadataConfig(), new \OpenIDConnect\Config\JwkSet(JsonConverter::decode($jwks)));

        // Register addition JWK
        $providerMetadata->addJwk($additionJwk->jsonSerialize());

        $expectedExp = time() + 3600;
        $expectedIat = time();

        $clientInformation = $this->createClientInformation([
            'client_id' => 'some-aud',
        ]);

        $payload = [
            'aud' => 'some-aud',
            'exp' => $expectedExp,
            'iat' => $expectedIat,
            'iss' => 'some-iss',
            'sub' => 'some-sub',
        ];

        $factory = new JwtFactory($providerMetadata, $clientInformation);

        $this->markTestIncomplete();

        $jws = $factory->createJwsBuilder()
            ->withPayload(JsonConverter::encode($payload))
            ->addSignature($additionJwk, ['alg' => 'HS256'])
            ->build();

        $target = new TokenSet($this->createFakeTokenSetParameter([
            'id_token' => (new CompactSerializer())->serialize($jws),
        ]));

        $target->setClientInformation($clientInformation);
        $target->setProviderMetadata($providerMetadata);
        $target->setJwtFactory(new JwtFactory($providerMetadata, $clientInformation));

        $this->markTestIncomplete();

        $actual = $target->idTokenClaims();

        $this->assertSame('some-aud', $actual->aud());
        $this->assertSame('some-iss', $actual->iss());
        $this->assertSame($expectedExp, $actual->exp());
        $this->assertSame($expectedIat, $actual->iat());
        $this->assertSame('some-sub', $actual->sub());
        $this->assertNull($actual->authTime());
        $this->assertNull($actual->nonce());
        $this->assertNull($actual->acr());
        $this->assertNull($actual->amr());
        $this->assertNull($actual->azp());
    }

    /**
     * @test
     */
    public function shouldThrowWhenCallValueWithDefaultKeys(): void
    {
        $jwk = JWKFactory::createRSAKey(1024, ['alg' => 'RS256']);
        $jwks = JsonConverter::encode(new JWKSet([$jwk]));

        $providerMetadata = new ProviderMetadata($this->createProviderMetadataConfig(), new \OpenIDConnect\Config\JwkSet(JsonConverter::decode($jwks)));

        $expectedExp = time() + 3600;
        $expectedIat = time();

        $clientInformation = $this->createClientInformation([
            'client_id' => 'some-aud',
        ]);

        $payload = [
            'aud' => 'some-aud',
            'exp' => $expectedExp,
            'iat' => $expectedIat,
            'iss' => 'some-iss',
            'sub' => 'some-sub',
        ];

        $factory = new JwtFactory($providerMetadata, $clientInformation);

        $jws = $factory->createJwsBuilder()
            ->withPayload(JsonConverter::encode($payload))
            ->addSignature($jwk, ['alg' => 'RS256'])
            ->build();

        $target = new TokenSet($this->createFakeTokenSetParameter([
            'id_token' => (new CompactSerializer())->serialize($jws),
        ]));

        $target->setClientInformation($clientInformation);
        $target->setProviderMetadata($providerMetadata);
        $target->setJwtFactory(new JwtFactory($providerMetadata, $clientInformation));

        $actual = $target->idTokenClaims();

        $this->assertSame('some-aud', $actual->aud());
        $this->assertSame('some-iss', $actual->iss());
        $this->assertSame($expectedExp, $actual->exp());
        $this->assertSame($expectedIat, $actual->iat());
        $this->assertSame('some-sub', $actual->sub());
        $this->assertNull($actual->authTime());
        $this->assertNull($actual->nonce());
        $this->assertNull($actual->acr());
        $this->assertNull($actual->amr());
        $this->assertNull($actual->azp());
    }
}
