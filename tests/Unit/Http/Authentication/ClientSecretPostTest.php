<?php

namespace Tests\Unit\Http\Authentication;

use Laminas\Diactoros\RequestFactory;
use OpenIDConnect\Http\Authentication\ClientSecretPost;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ClientSecretPostTest extends TestCase
{
    #[Test]
    public function shouldReturnClientSecretParameter(): void
    {
        $target = new ClientSecretPost('c', 's');

        $actual = $target->processRequest((new RequestFactory())->createRequest('GET', 'whatever'));

        $this->assertStringContainsString('client_id=c', (string)$actual->getBody());
        $this->assertStringContainsString('client_secret=s', (string)$actual->getBody());
    }

    #[Test]
    public function shouldEncodeSpaceAsPlusSignInBody(): void
    {
        $target = new ClientSecretPost('c', 's t');

        $actual = $target->processRequest((new RequestFactory())->createRequest('GET', 'whatever'));

        $this->assertStringContainsString('client_secret=s+t', (string)$actual->getBody());
    }
}
