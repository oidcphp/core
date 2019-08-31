<?php

namespace Tests\Core\Metadata;

use OpenIDConnect\Metadata\MetadataAwareTraits;
use Tests\TestCase;

class MetadataAwareTraitTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnNewObjectWhenUsingWithMetadata(): void
    {
        /** @var MetadataAwareTraits $target */
        $target = $this->getObjectForTrait(MetadataAwareTraits::class);

        $actual = $target->withMetadata('some', 'value');

        $this->assertNotSame($target, $actual);
        $this->assertSame('value', $actual->offsetGet('some'));
    }
}
