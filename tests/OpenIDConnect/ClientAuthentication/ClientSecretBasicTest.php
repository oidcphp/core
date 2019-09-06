<?php

namespace Tests\OpenIDConnect\ClientAuthentication;

use GuzzleHttp\Psr7\Request;
use OpenIDConnect\ClientAuthentication\ClientSecretBasic;
use Tests\TestCase;

class ClientSecretBasicTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnClientSecretParameterOnHeader(): void
    {
        $expected = base64_encode('c:s');
        $target = new ClientSecretBasic();

        $actual = $target->withClientAuthentication(new Request('GET', 'whatever'), 'c', 's');

        $this->assertStringContainsString($expected, (string)$actual->getHeaderLine('Authorization'));
    }
}
