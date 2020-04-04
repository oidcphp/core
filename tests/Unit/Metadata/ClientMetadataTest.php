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
            'client_id' => 'some_id',
            'client_id_issued_at' => 1000,
            'client_secret' => 'some_secret',
            'client_secret_expires_at' => 0,
        ]);

        $this->assertTrue($target->has('some'));
        $this->assertFalse($target->has('whatever'));

        $this->assertSame('some_id', $target->id());
        $this->assertSame(1000, $target->issuedAt());
        $this->assertSame('some_secret', $target->secret());
        $this->assertSame(0, $target->expiresAt());
    }
}
