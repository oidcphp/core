<?php

namespace Tests\OAuth2\ClientAuthentication;

use Http\Factory\Guzzle\RequestFactory;
use OpenIDConnect\OAuth2\ClientAuthentication\ClientSecretBasic;
use PHPUnit\Framework\TestCase;

class ClientSecretBasicTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnClientSecretParameterOnHeader(): void
    {
        $expected = base64_encode('c:s');
        $target = new ClientSecretBasic('c', 's');

        $actual = $target->processRequest((new RequestFactory())->createRequest('GET', 'whatever'));

        $this->assertStringContainsString($expected, (string)$actual->getHeaderLine('Authorization'));
    }
}
