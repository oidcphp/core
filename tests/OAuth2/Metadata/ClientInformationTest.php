<?php

namespace Tests\OAuth2\Metadata;

use OpenIDConnect\OAuth2\Metadata\ClientInformation;
use Tests\TestCase;

class ClientInformationTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeOkayWhenNewInstance(): void
    {
        $target = new ClientInformation([
            'client_id' => 'some_id',
            'client_id_issued_at' => 1000,
            'client_secret' => 'some_secret',
            'client_secret_expires_at' => 0,
        ]);

        $this->assertSame('some_id', $target->id());
        $this->assertSame(1000, $target->issuedAt());
        $this->assertSame('some_secret', $target->secret());
        $this->assertSame(0, $target->expiresAt());
    }
}
