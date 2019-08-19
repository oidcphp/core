<?php

namespace Tests\Core\Metadata;

use DomainException;
use InvalidArgumentException;
use Jose\Component\Checker\InvalidHeaderException;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\Algorithm\PS256;
use Jose\Component\Signature\Algorithm\RS256;
use OpenIDConnect\Jwt\Factory;
use OpenIDConnect\Metadata\ProviderMetadata;
use OutOfBoundsException;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnAlgorithmManagerContainAlgorithms(): void
    {
        $target = new Factory($this->createProviderMetadata([
            'id_token_signing_alg_values_supported' => ['RS256', 'ES256'],
        ]));

        $actual = $target->createAlgorithmManager();

        $this->assertInstanceOf(RS256::class, $actual->get('RS256'));
        $this->assertInstanceOf(ES256::class, $actual->get('ES256'));
    }

    /**
     * @test
     */
    public function shouldReturnAlgorithmManagerContainEncryptionAlgorithms(): void
    {
        $target = new Factory($this->createProviderMetadata([
            'id_token_encryption_alg_values_supported' => ['RS256', 'ES256', 'PS256'],
            'id_token_signing_alg_values_supported' => ['RS256', 'ES256'],
        ]));

        $actual = $target->createAlgorithmManager();

        $this->assertInstanceOf(RS256::class, $actual->get('RS256'));
        $this->assertInstanceOf(ES256::class, $actual->get('ES256'));
        $this->assertInstanceOf(PS256::class, $actual->get('PS256'));
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenAlgorithmIsNotFound(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $target = new Factory($this->createProviderMetadata([
            'id_token_signing_alg_values_supported' => ['RS256'],
        ]));

        $actual = $target->createAlgorithmManager();

        $actual->get('ES256');
    }

    /**
     * @test
     */
    public function shouldReturnHeaderCheckManagerContainAlgorithms(): void
    {
        $target = new Factory($this->createProviderMetadata([
            'id_token_encryption_alg_values_supported' => ['RS256', 'PS256'],
            'id_token_signing_alg_values_supported' => ['RS256', 'ES256'],
        ]));

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

        $target = new Factory($this->createProviderMetadata());

        $actual = $target->createHeaderCheckerManager()->getCheckers()['alg'];

        $actual->checkHeader('HS256');
    }
}
