<?php

namespace Tests\Core\ClientAuthentication;

use GuzzleHttp\Psr7\Request;
use OpenIDConnect\Core\ClientAuthentication\ClientSecretPost;
use Tests\TestCase;

class ClientSecretPostTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnClientSecretParameter(): void
    {
        $target = new ClientSecretPost();

        $actual = $target->withClientAuthentication(new Request('GET', 'whatever'), 'c', 's');

        $this->assertStringContainsString('client_id=c', (string)$actual->getBody());
        $this->assertStringContainsString('client_secret=s', (string)$actual->getBody());
    }
}
