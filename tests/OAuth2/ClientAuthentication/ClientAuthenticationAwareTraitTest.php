<?php

namespace Tests\OAuth2\ClientAuthentication;

use OpenIDConnect\OAuth2\ClientAuthentication\ClientAuthenticationAwareTrait;
use OpenIDConnect\OAuth2\ClientAuthentication\ClientSecretBasic;
use OpenIDConnect\OAuth2\ClientAuthentication\ClientSecretPost;
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

        $actual = $target->resolveClientAuthenticationByDefault('c', 's');

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

        $actual = $target->resolveClientAuthenticationByDefault('c', 's');

        $this->assertInstanceOf(ClientSecretPost::class, $actual);
    }
}
