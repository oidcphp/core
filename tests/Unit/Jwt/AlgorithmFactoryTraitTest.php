<?php

namespace Tests\Unit\Jwt;

use Jose\Component\Core\Algorithm;
use Jose\Component\Encryption\Algorithm\KeyEncryption\Dir;
use Jose\Component\Signature\Algorithm\EdDSA;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\Algorithm\RS256;
use OpenIDConnect\Jwt\Concerns\AlgorithmFactory;
use OutOfRangeException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

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

    /**
     * @test
     */
    public function shouldThrowExceptionWhenAliasIsUnknown(): void
    {
        $this->expectException(OutOfRangeException::class);

        $this->target->createAlgorithm('Whatever');
    }

    /**
     * @test
     */
    public function shouldReturnInstanceWhenAliasIsEdDSA(): void
    {
        if (!extension_loaded('sodium')) {
            $this->markTestSkipped('ext-sodium is not loaded');
        }

        $this->assertInstanceOf(EdDSA::class, $this->target->createAlgorithm('EdDSA'));
        $this->assertSame('EdDSA', $this->target->createAlgorithm('EdDSA')->name());
    }

    /**
     * @test
     */
    public function shouldReturnInstanceWhenAliasIsDir(): void
    {
        $this->assertInstanceOf(Dir::class, $this->target->createAlgorithm('dir'));
    }

    /**
     * @test
     * @dataProvider experimentalAlgorithms
     */
    public function shouldReturnInstanceWhenAliasIsExperimentalAlgorithm(string $alias): void
    {
        if (!class_exists('Jose\\Experimental\\KeyEncryption\\RSAOAEP384')) {
            $this->markTestSkipped('web-token/jwt-experimental is not installed');
        }

        $this->assertTrue($this->target->hasAlgorithm($alias));
        $this->assertSame($alias, $this->target->createAlgorithm($alias)->name());
    }

    public function experimentalAlgorithms(): iterable
    {
        yield ['RSA-OAEP-384'];
        yield ['RSA-OAEP-512'];
        yield ['ES256K'];
        yield ['HS1'];
        yield ['HS256/64'];
        yield ['RS1'];
    }

    /**
     * @test
     */
    public function shouldReturnFalseWhenAlgorithmCannotBeCreated(): void
    {
        $this->assertTrue($this->target->hasAlgorithm('RS256'));
        $this->assertFalse($this->target->hasAlgorithm('Whatever'));
    }

    /**
     * @test
     */
    public function shouldReturnRegisteredInstanceWhenCallWithAlgorithm(): void
    {
        $custom = $this->createAlgorithmStub('CUSTOM');
        $override = $this->createAlgorithmStub('RS256');

        $this->assertFalse($this->target->hasAlgorithm('CUSTOM'));

        $this->target->withAlgorithm($custom)->withAlgorithm([$override]);

        $this->assertTrue($this->target->hasAlgorithm('CUSTOM'));
        $this->assertSame($custom, $this->target->createAlgorithm('CUSTOM'));
        $this->assertSame($override, $this->target->createAlgorithm('RS256'));
    }

    /**
     * hasAlgorithm() hides the broken alias, so make sure every alias is still creatable
     *
     * @test
     */
    public function shouldCreateEveryAliasWhenAllPackagesAreInstalled(): void
    {
        if (
            !class_exists('Jose\\Experimental\\KeyEncryption\\RSAOAEP384')
            || !class_exists('AESKW\\A128KW')
            || !extension_loaded('sodium')
        ) {
            $this->markTestSkipped('Optional packages or extensions are not installed');
        }

        $method = new ReflectionMethod($this->target, 'algorithmAliases');
        $method->setAccessible(true);

        foreach (array_keys($method->invoke($this->target)) as $alias) {
            $this->assertTrue($this->target->hasAlgorithm($alias), "Alias '$alias' cannot be created");
        }
    }

    private function createAlgorithmStub(string $name): Algorithm
    {
        $stub = $this->createStub(Algorithm::class);
        $stub->method('name')->willReturn($name);

        return $stub;
    }
}
