<?php

namespace Tests\Unit\Http\Authentication;

use OpenIDConnect\Http\Authentication\ClientAuthenticationAwareTrait;
use OpenIDConnect\Http\Authentication\ClientSecretBasic;
use OpenIDConnect\Http\Authentication\ClientSecretPost;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ClientAuthenticationAwareTraitTest extends TestCase
{
    #[Test]
    public function shouldReturnClientSecretBasicDefault(): void
    {
        /** @var ClientAuthenticationAwareTrait $target */
        $target = $this->getMockForTrait(ClientAuthenticationAwareTrait::class);

        $actual = $target->resolveClientAuthentication('c', 's');

        $this->assertInstanceOf(ClientSecretBasic::class, $actual);
    }

    #[Test]
    public function shouldReturnPrepareAuthenticationWhenSetBefore(): void
    {
        /** @var ClientAuthenticationAwareTrait $target */
        $target = $this->getMockForTrait(ClientAuthenticationAwareTrait::class);
        $target->setClientAuthentication(new ClientSecretPost('c', 's'));

        $actual = $target->resolveClientAuthentication('c', 's');

        $this->assertInstanceOf(ClientSecretPost::class, $actual);
    }
}
