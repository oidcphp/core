<?php

namespace Tests\OpenIDConnect;

use OpenIDConnect\Client;
use OpenIDConnect\Factory;
use OpenIDConnect\Jwt\JwtFactory;
use OpenIDConnect\Token\TokenSet;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnJwtFactory(): void
    {
        $factory = new Factory($this->createProviderMetadata(), $this->createClientMetadata());

        $this->assertInstanceOf(JwtFactory::class, $factory->createJwtFactory());
    }

    /**
     * @test
     */
    public function shouldReturnClient(): void
    {
        $factory = new Factory($this->createProviderMetadata(), $this->createClientMetadata());

        $this->assertInstanceOf(Client::class, $factory->createOpenIDConnectClient());
    }

    /**
     * @test
     */
    public function shouldReturnTokenSet(): void
    {
        $factory = new Factory($this->createProviderMetadata(), $this->createClientMetadata());

        $this->assertInstanceOf(TokenSet::class, $factory->createTokenSet([
            'access_token' => 'whatever',
        ]));
    }
}
