<?php

namespace Tests\Core\Metadata;

use OpenIDConnect\Metadata\MetadataTraits;
use Tests\TestCase;

class MetadataTraitTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnNewObjectWhenUsingWithMetadata(): void
    {
        /** @var MetadataTraits $target */
        $target = $this->getObjectForTrait(MetadataTraits::class);

        $actual = $target->withMetadata('some', 'value');

        $this->assertNotSame($target, $actual);
        $this->assertSame('value', $actual->offsetGet('some'));
    }
}
