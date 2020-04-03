<?php

namespace Tests\OAuth2\ClientAuthentication;

use Laminas\Diactoros\RequestFactory;
use OpenIDConnect\Http\Authentication\ClientSecretBasic;
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
