<?php

namespace Tests\Unit\Http\Authentication;

use OpenIDConnect\Http\Authentication\ClientAuthenticationAwareTrait;
use OpenIDConnect\Http\Authentication\ClientSecretBasic;
use OpenIDConnect\Http\Authentication\ClientSecretPost;
use PHPUnit\Framework\TestCase;

class ClientAuthenticationAwareTraitTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnClientSecretBasicDefault(): void
    {
        /** @var ClientAuthenticationAwareTrait $target */
        $target = $this->getMockForTrait(ClientAuthenticationAwareTrait::class);

        $actual = $target->resolveClientAuthentication('c', 's');

        $this->assertInstanceOf(ClientSecretBasic::class, $actual);
    }

    /**
     * @test
     */
    public function shouldReturnPrepareAuthenticationWhenSetBefore(): void
    {
        /** @var ClientAuthenticationAwareTrait $target */
        $target = $this->getMockForTrait(ClientAuthenticationAwareTrait::class);
        $target->setClientAuthentication(new ClientSecretPost('c', 's'));

        $actual = $target->resolveClientAuthentication('c', 's');

        $this->assertInstanceOf(ClientSecretPost::class, $actual);
    }
}
