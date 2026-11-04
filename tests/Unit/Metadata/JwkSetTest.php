<?php

namespace Tests\Unit\Metadata;

use InvalidArgumentException;
use OpenIDConnect\Jwt\JwkSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(JwkSet::class)]
class JwkSetTest extends TestCase
{
    #[Test]
    public function shouldBeEmptyWhenNoJwk(): void
    {
        $target = new JwkSet();

        $this->assertSame(['keys' => []], $target->toArray());
    }

    #[Test]
    public function shouldBeInitWhenProvisionJwkSet(): void
    {
        $target = new JwkSet([
            'keys' => [
                ['kid' => 'whatever'],
                ['foo' => 'bar'],
            ],
        ]);

        $this->assertSame(['keys' => [['kid' => 'whatever'], ['foo' => 'bar']]], $target->toArray());
        $this->assertSame(['kid' => 'whatever'], $target->get('whatever'));
        $this->assertSame(['foo' => 'bar'], $target->get(0));
    }

    #[Test]
    public function shouldSaveOneKeyWhenAddJwk(): void
    {
        $target = new JwkSet();
        $target->add(['kid' => 'whatever']);

        $this->assertSame(['keys' => [['kid' => 'whatever']]], $target->toArray());
        $this->assertSame(['keys' => [['kid' => 'whatever']]], $target->jsonSerialize());
    }

    #[Test]
    public function shouldGetSpecifyKeyWhenCallGet(): void
    {
        $target = new JwkSet();
        $target->add(['kid' => 'whatever']);

        $this->assertSame(['kid' => 'whatever'], $target->get('whatever'));
    }

    #[Test]
    public function shouldUseIntKeyWhenNoKidInJwk(): void
    {
        $target = new JwkSet();
        $target->add(['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar'], $target->get(0));
    }

    #[Test]
    public function shouldThrowExceptionWhenNoIndex(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $target = new JwkSet();

        $target->get(0);
    }
}
