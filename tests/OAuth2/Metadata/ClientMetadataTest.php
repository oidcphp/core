<?php

namespace Tests\OAuth2\Metadata;

use OpenIDConnect\OAuth2\Metadata\ClientMetadata;
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
