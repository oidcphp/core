<?php

namespace Tests\Unit\Traits;

use BadMethodCallException;
use DomainException;
use OpenIDConnect\Traits\ParameterTrait;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ParameterTraitTest extends TestCase
{
    #[Test]
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

    #[Test]
    public function shouldMergeItemsWhenCallMerge(): void
    {
        /** @var ParameterTrait $target */
        $target = $this->getMockForTrait(ParameterTrait::class);

        $actual = $target->merge(['foo' => 1, 'bar' => 2])
            ->merge(['foo' => 3, 'baz' => 4]);


        $this->assertSame(3, $actual->get('foo'));
        $this->assertSame(2, $actual->get('bar'));
        $this->assertSame(4, $actual->get('baz'));
    }

    #[Test]
    public function shouldReturnCorrectValueWhenCallHas(): void
    {
        /** @var ParameterTrait $target */
        $target = $this->getMockForTrait(ParameterTrait::class);

        $actual = $target->with('some', 'value');

        $this->assertTrue($actual->has('some'));
        $this->assertFalse($actual->has('whatever'));
    }

    #[Test]
    public function shouldReturnDefaultValueWhenGet(): void
    {
        /** @var ParameterTrait $target */
        $target = $this->getMockForTrait(ParameterTrait::class);

        $this->assertSame('d', $target->get('some', 'd'));
    }

    #[Test]
    public function shouldReturnNewObjectWhenUsingWithMetadata(): void
    {
        /** @var ParameterTrait $target */
        $target = $this->getMockForTrait(ParameterTrait::class);

        $actual = $target->with('some', 'value');

        $this->assertNotSame($target, $actual);
        $this->assertSame('value', $actual->get('some'));
    }

    #[Test]
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

    #[Test]
    public function shouldThrowExceptionWhenAssertWithoutKey(): void
    {
        $this->expectException(DomainException::class);

        /** @var ParameterTrait $target */
        $target = $this->getMockForTrait(ParameterTrait::class);

        $target->assertHasKey('not-exist');
    }

    #[Test]
    public function shouldThrowExceptionWhenAssertWithoutKeys(): void
    {
        $this->expectException(DomainException::class);

        /** @var ParameterTrait $target */
        $target = $this->getMockForTrait(ParameterTrait::class);

        $actual = $target->with('foo', 'bar');

        $actual->assertHasKeys(['foo', 'not-exist']);
    }

    #[Test]
    public function shouldReturnValueWhenUsingMagicCall(): void
    {
        /** @var ParameterTrait $target */
        $target = $this->getMockForTrait(ParameterTrait::class);

        $actual = $target->with('foo', 'bar');

        $this->assertSame('bar', $actual->foo());

        // Run again will save to cache
        $this->assertSame('bar', $actual->foo());
    }

    #[Test]
    public function shouldReturnValueWhenUsingMagicCallWithCamelCase(): void
    {
        /** @var ParameterTrait $target */
        $target = $this->getMockForTrait(ParameterTrait::class);

        $actual = $target->with('camel_case', 'bar');

        $this->assertSame('bar', $actual->camelCase());
    }

    #[Test]
    public function shouldThrowBadMethodCallExceptionWhenNoParameter(): void
    {
        $this->expectException(BadMethodCallException::class);

        /** @var ParameterTrait $target */
        $target = $this->getMockForTrait(ParameterTrait::class);

        $target->notExist();
    }
}
