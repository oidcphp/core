<?php

namespace Tests\Core\Token;

use InvalidArgumentException;
use Jose\Component\Core\JWKSet;
use Jose\Component\Core\Util\JsonConverter;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\JWS;
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
        ]), $this->createProviderMetadata());

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

        $target = new TokenSet($this->createFakeTokenSetParameter(), $this->createProviderMetadata());

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

        $payload = [
            'aud' => 'some-aud',
            'exp' => time() + 3600,
            'iat' => time(),
            'iss' => 'some-iss',
            'sub' => 'some-sub',
        ];

        $jws = $providerMetadata->createJwtFactory()->createJwsBuilder()
            ->withPayload(JsonConverter::encode($payload))
            ->addSignature($jwk, ['alg' => 'RS256'])
            ->build();

        $target = new TokenSet($this->createFakeTokenSetParameter([
            'id_token' => (new CompactSerializer())->serialize($jws),
        ]), $providerMetadata);

        $this->assertInstanceOf(JWS::class, $target->verifyIdToken());
    }
}
