<?php

namespace Tests\OAuth2\Metadata;

use OpenIDConnect\Config\ClientMetadata;
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
