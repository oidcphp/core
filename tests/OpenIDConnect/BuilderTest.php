<?php

namespace Tests\OpenIDConnect;

use OpenIDConnect\Client;
use OpenIDConnect\Builder;
use OpenIDConnect\Jwt\JwtFactory;
use OpenIDConnect\Token\TokenSet;
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
