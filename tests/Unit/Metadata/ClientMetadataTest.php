<?php

namespace Tests\Unit\Metadata;

use OpenIDConnect\Metadata\ClientMetadata;
use Tests\TestCase;

class ClientMetadataTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeOkayWhenNewInstance(): void
    {
        $target = new ClientMetadata([
            'some' => 'value',
        ]);

        $this->assertTrue($target->has('some'));
        $this->assertFalse($target->has('whatever'));
    }
}
