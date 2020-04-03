<?php

namespace Tests\Unit\Jwt;

use InvalidArgumentException;
use Jose\Component\Checker\InvalidHeaderException;
use Jose\Component\Core\Util\JsonConverter;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A128GCM;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A192CBCHS384;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\Serializer\CompactSerializer;
use OpenIDConnect\Jwt\JwtFactory;
use OutOfRangeException;
use Tests\TestCase;

class JwtFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldThrowExceptionWhenAlgorithmIsNotDefine(): void
    {
        $this->expectException(OutOfRangeException::class);

        $target = new JwtFactory($this->createProviderMetadata([
            'id_token_signing_alg_values_supported' => ['Whatever'],
        ]), $this->createClientInformation());

        $actual = $target->createAlgorithmManager();

        $this->assertInstanceOf(RS256::class, $actual->get('RS256'));
        $this->assertInstanceOf(ES256::class, $actual->get('ES256'));
    }

    /**
     * @test
     */
    public function shouldReturnAlgorithmManagerContainAlgorithms(): void
    {
        $target = new JwtFactory($this->createProviderMetadata([
            'id_token_signing_alg_values_supported' => ['RS256', 'ES256'],
        ]), $this->createClientInformation());

        $actual = $target->createAlgorithmManager();

        $this->assertInstanceOf(RS256::class, $actual->get('RS256'));
        $this->assertInstanceOf(ES256::class, $actual->get('ES256'));
    }

    /**
     * @test
     */
    public function shouldReturnAlgorithmManagerContainEncryptionAlgorithms(): void
    {
        $target = new JwtFactory($this->createProviderMetadata([
            'id_token_signing_alg_values_supported' => ['RS256', 'ES256'],
            'id_token_encryption_alg_values_supported' => ['A128GCM'],
            'id_token_encryption_enc_values_supported' => ['A192CBC-HS384'],
        ]), $this->createClientInformation());

        $actual = $target->createAlgorithmManager();

        $this->assertInstanceOf(RS256::class, $actual->get('RS256'));
        $this->assertInstanceOf(ES256::class, $actual->get('ES256'));
        $this->assertInstanceOf(A128GCM::class, $actual->get('A128GCM'));
        $this->assertInstanceOf(A192CBCHS384::class, $actual->get('A192CBC-HS384'));
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenAlgorithmIsNotFound(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $target = new JwtFactory($this->createProviderMetadata([
            'id_token_signing_alg_values_supported' => ['RS256'],
        ]), $this->createClientInformation());

        $actual = $target->createAlgorithmManager();

        $actual->get('ES256');
    }

    /**
     * @test
     */
    public function shouldReturnHeaderCheckManagerContainAlgorithms(): void
    {
        $target = new JwtFactory($this->createProviderMetadata([
            'id_token_encryption_alg_values_supported' => ['RS256', 'PS256'],
            'id_token_signing_alg_values_supported' => ['RS256', 'ES256'],
        ]), $this->createClientInformation());

        $actual = $target->createHeaderCheckerManager()->getCheckers()['alg'];

        $this->assertNull($actual->checkHeader('RS256'));
        $this->assertNull($actual->checkHeader('PS256'));
        $this->assertNull($actual->checkHeader('ES256'));
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenAlgorithmsNotFoundInHeaderCheckerManager(): void
    {
        $this->expectException(InvalidHeaderException::class);
        $this->expectExceptionMessage('Unsupported algorithm');

        $target = new JwtFactory($this->createProviderMetadata(), $this->createClientInformation());

        $actual = $target->createHeaderCheckerManager()->getCheckers()['alg'];

        $actual->checkHeader('HS256');
    }

    /**
     * A full flow for sign and verify
     *
     * @test
     */
    public function shouldBuildJwtAndVerifyAndStringAndSerializeStringAndLoadUsingRS256(): void
    {
        $target = new JwtFactory($this->createProviderMetadata(), $this->createClientInformation());

        $jwk = JWKFactory::createRSAKey(1024, ['alg' => 'RS256']);

        $builder = $target->createJwsBuilder();

        $jws = $builder->withPayload(JsonConverter::encode([]))
            ->addSignature($jwk, ['alg' => 'RS256'])
            ->build();

        $verifier = $target->createJwsVerifier();

        $this->assertTrue($verifier->verifyWithKey($jws, $jwk, 0));

        $token = (new CompactSerializer())->serialize($jws);

        // {"alg": "RS256"} + []
        $this->assertStringStartsWith('eyJhbGciOiJSUzI1NiJ9.W10', $token);

        $loader = $target->createJwsLoader();

        $jws = $loader->loadAndVerifyWithKey($token, $jwk, $sign);

        $this->assertSame('[]', $jws->getPayload());
    }
}
