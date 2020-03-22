<?php

namespace Tests\OAuth2\Traits;

use BadMethodCallException;
use DomainException;
use OpenIDConnect\OAuth2\Traits\ParameterTrait;
use Tests\TestCase;

class ParameterTraitTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnCorrectValueWhenCallAppend(): void
    {
        /** @var ParameterTrait $target */
        $target = $this->getMockForTrait(ParameterTrait::class);

        $actual = $target->append('value')
            ->append('value')
            ->append('value');

        $this->assertTrue($actual->has(0));
        $this->assertTrue($actual->has(1));
        $this->assertTrue($actual->has(2));

        $this->assertSame('value', $actual->get(0));
        $this->assertSame('value', $actual->get(1));
        $this->assertSame('value', $actual->get(2));
    }

    /**
     * @test
     */
    public function shouldReturnCorrectValueWhenCallHas(): void
    {
        /** @var ParameterTrait $target */
        $target = $this->getMockForTrait(ParameterTrait::class);

        $actual = $target->with('some', 'value');

        $this->assertTrue($actual->has('some'));
        $this->assertFalse($actual->has('whatever'));
    }

    /**
     * @test
     */
    public function shouldReturnDefaultValueWhenGet(): void
    {
        /** @var ParameterTrait $target */
        $target = $this->getMockForTrait(ParameterTrait::class);

        $this->assertSame('d', $target->get('some', 'd'));
    }

    /**
     * @test
     */
    public function shouldReturnNewObjectWhenUsingWithMetadata(): void
    {
        /** @var ParameterTrait $target */
        $target = $this->getMockForTrait(ParameterTrait::class);

        $actual = $target->with('some', 'value');

        $this->assertNotSame($target, $actual);
        $this->assertSame('value', $actual->get('some'));
    }

    /**
     * @test
     */
    public function shouldReturnArrayWhenCallToArray(): void
    {
        /** @var ParameterTrait $target */
        $target = $this->getMockForTrait(ParameterTrait::class);

        $actual = $target->with('foo', 'a')
            ->with('bar', 'z');

        $expected = [
            'foo' => 'a',
            'bar' => 'z',
        ];

        $this->assertSame($expected, $actual->toArray());
        $this->assertSame($expected, $actual->jsonSerialize());
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenAssertWithoutKey(): void
    {
        $this->expectException(DomainException::class);

        /** @var ParameterTrait $target */
        $target = $this->getMockForTrait(ParameterTrait::class);

        $target->assertHasKey('not-exist');
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenAssertWithoutKeys(): void
    {
        $this->expectException(DomainException::class);

        /** @var ParameterTrait $target */
        $target = $this->getMockForTrait(ParameterTrait::class);

        $actual = $target->with('foo', 'bar');

        $actual->assertHasKeys(['foo', 'not-exist']);
    }

    /**
     * @test
     */
    public function shouldReturnValueWhenUsingMagicCall(): void
    {
        /** @var ParameterTrait $target */
        $target = $this->getMockForTrait(ParameterTrait::class);

        $actual = $target->with('foo', 'bar');

        $this->assertSame('bar', $actual->foo());

        // Run again will save to cache
        $this->assertSame('bar', $actual->foo());
    }

    /**
     * @test
     */
    public function shouldReturnValueWhenUsingMagicCallWithCamelCase(): void
    {
        /** @var ParameterTrait $target */
        $target = $this->getMockForTrait(ParameterTrait::class);

        $actual = $target->with('camel_case', 'bar');

        $this->assertSame('bar', $actual->camelCase());
    }

    /**
     * @test
     */
    public function shouldThrowBadMethodCallExceptionWhenNoParameter(): void
    {
        $this->expectException(BadMethodCallException::class);

        /** @var ParameterTrait $target */
        $target = $this->getMockForTrait(ParameterTrait::class);

        $target->notExist();
    }
}
