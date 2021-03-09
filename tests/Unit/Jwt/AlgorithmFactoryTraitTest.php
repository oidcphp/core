<?php

namespace Tests\Unit\Jwt;

use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\Algorithm\RS256;
use OpenIDConnect\Jwt\Concerns\AlgorithmFactory;
use PHPUnit\Framework\TestCase;

class AlgorithmFactoryTraitTest extends TestCase
{
    /**
     * @var AlgorithmFactory
     */
    private $target;

    protected function setUp(): void
    {
        $this->target = $this->getObjectForTrait(AlgorithmFactory::class);
    }

    protected function tearDown(): void
    {
        $this->target = null;
    }

    /**
     * @test
     */
    public function shouldReturnInstanceWhenCallCreateSignatureAlgorithm(): void
    {
        $this->assertInstanceOf(RS256::class, $this->target->createAlgorithm('RS256'));
    }

    /**
     * @test
     */
    public function shouldReturnInstancesWhenCallCreateSignatureAlgorithms(): void
    {
        $actual = $this->target->createAlgorithms([
            'ES256',
            'RS256',
        ]);

        $this->assertInstanceOf(ES256::class, $actual[0]);
        $this->assertInstanceOf(RS256::class, $actual[1]);
    }
}
