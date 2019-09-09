<?php

namespace Tests\OpenIDConnect\Token;

use InvalidArgumentException;
use Jose\Component\Core\JWKSet;
use Jose\Component\Core\Util\JsonConverter;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Serializer\CompactSerializer;
use OpenIDConnect\Metadata\ProviderMetadata;
use OpenIDConnect\Token\TokenSet;
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
        ]), $this->createProviderMetadata(), $this->createClientRegistration());

        $this->assertSame('some-access-token', $target->accessToken());
        $this->assertSame(3600, $target->expiresIn());
        $this->assertSame('some-id-token', $target->idToken());
        $this->assertSame('some-refresh-token', $target->refreshToken());
        $this->assertSame(['some-scope'], $target->scope());
        $this->assertSame('some-addition', $target->values('addition'));

        $this->assertTrue($target->hasExpiresIn());
        $this->assertTrue($target->hasIdToken());
        $this->assertTrue($target->hasRefreshToken());
        $this->assertTrue($target->hasScope());
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

        $providerMetadata = new ProviderMetadata($this->createProviderMetadataConfig(), JsonConverter::decode($jwks));

        // Register addition JWK
        $providerMetadata->withJwkInstances($additionJwk);

        $expectedExp = time() + 3600;
        $expectedIat = time();

        $clientMetadata = $this->createClientRegistration([
            'client_id' => 'some-aud',
        ]);

        $payload = [
            'aud' => 'some-aud',
            'exp' => $expectedExp,
            'iat' => $expectedIat,
            'iss' => 'some-iss',
            'sub' => 'some-sub',
        ];

        $jws = $providerMetadata->createJwtFactory($clientMetadata)->createJwsBuilder()
            ->withPayload(JsonConverter::encode($payload))
            ->addSignature($additionJwk, ['alg' => 'HS256'])
            ->build();

        $target = new TokenSet($this->createFakeTokenSetParameter([
            'id_token' => (new CompactSerializer())->serialize($jws),
        ]), $providerMetadata, $clientMetadata);

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

    public function defaultKeys()
    {
        return array_map(static function ($key) {
            return [$key];
        }, TokenSet::DEFAULT_KEYS);
    }

    /**
     * @dataProvider defaultKeys
     * @test
     */
    public function shouldThrowExceptionWhenCallValueWithDefaultKeys($key): void
    {
        $this->expectException(InvalidArgumentException::class);

        $target = new TokenSet(
            $this->createFakeTokenSetParameter(),
            $this->createProviderMetadata(),
            $this->createClientRegistration()
        );

        $target->values($key);
    }

    /**
     * @test
     */
    public function shouldThrowWhenCallValueWithDefaultKeys(): void
    {
        $jwk = JWKFactory::createRSAKey(1024, ['alg' => 'RS256']);
        $jwks = JsonConverter::encode(new JWKSet([$jwk]));

        $providerMetadata = new ProviderMetadata($this->createProviderMetadataConfig(), JsonConverter::decode($jwks));

        $expectedExp = time() + 3600;
        $expectedIat = time();

        $clientMetadata = $this->createClientRegistration([
            'client_id' => 'some-aud',
        ]);

        $payload = [
            'aud' => 'some-aud',
            'exp' => $expectedExp,
            'iat' => $expectedIat,
            'iss' => 'some-iss',
            'sub' => 'some-sub',
        ];

        $jws = $providerMetadata->createJwtFactory($clientMetadata)->createJwsBuilder()
            ->withPayload(JsonConverter::encode($payload))
            ->addSignature($jwk, ['alg' => 'RS256'])
            ->build();

        $target = new TokenSet($this->createFakeTokenSetParameter([
            'id_token' => (new CompactSerializer())->serialize($jws),
        ]), $providerMetadata, $clientMetadata);

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
