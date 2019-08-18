<?php

namespace Tests\Core\Jwt;

use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\Algorithm\RS256;
use OpenIDConnect\Jwt\AlgorithmFactory;
use PHPUnit\Framework\TestCase;

class AlgorithmFactoryTest extends TestCase
{
    /**
     * @var AlgorithmFactory
     */
    private $target;

    protected function setUp(): void
    {
        $this->target = new AlgorithmFactory();
    }

    protected function tearDown(): void
    {
        $this->target = null;
    }

    /**
     * @test
     */
    public function shouldBeOkayWhenCreateAlgorithmManager(): void
    {
        $actual = $this->target->createAlgorithmManager([
            'ES256',
            'RS256',
        ]);

        $this->assertInstanceOf(ES256::class, $actual->get('ES256'));
        $this->assertInstanceOf(RS256::class, $actual->get('RS256'));
    }

    /**
     * @test
     */
    public function shouldReturnInstanceWhenCallCreateSignatureAlgorithm(): void
    {
        $this->assertInstanceOf(RS256::class, $this->target->createSignatureAlgorithm('RS256'));
    }

    /**
     * @test
     */
    public function shouldReturnInstancesWhenCallCreateSignatureAlgorithms(): void
    {
        $actual = $this->target->createSignatureAlgorithms([
            'ES256',
            'RS256',
        ]);

        $this->assertInstanceOf(ES256::class, $actual[0]);
        $this->assertInstanceOf(RS256::class, $actual[1]);
    }
}
