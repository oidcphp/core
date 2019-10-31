<?php

namespace Tests\Core;

use OpenIDConnect\Core\Builder;
use OpenIDConnect\Core\Client;
use OpenIDConnect\Core\Jwt\JwtFactory;
use OpenIDConnect\Core\Token\TokenSet;
use Tests\TestCase;

class BuilderTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnJwtFactory(): void
    {
        $factory = new Builder($this->createProviderMetadata(), $this->createClientRegistration());

        $this->assertInstanceOf(JwtFactory::class, $factory->createJwtFactory());
    }

    /**
     * @test
     */
    public function shouldReturnClient(): void
    {
        $factory = Builder::create($this->createProviderMetadata(), $this->createClientRegistration())
            ->useDefaultContainer();

        $this->assertInstanceOf(Client::class, $factory->createOpenIDConnectClient());
    }

    /**
     * @test
     */
    public function shouldReturnTokenSet(): void
    {
        $factory = new Builder($this->createProviderMetadata(), $this->createClientRegistration());

        $this->assertInstanceOf(TokenSet::class, $factory->createTokenSet([
            'access_token' => 'whatever',
        ]));
    }
}
