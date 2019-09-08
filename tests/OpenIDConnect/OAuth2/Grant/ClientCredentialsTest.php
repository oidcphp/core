<?php

namespace Tests\OpenIDConnect\OAuth2\Grant;

use OpenIDConnect\Exceptions\RelyingPartyException;
use OpenIDConnect\OAuth2\Grant\ClientCredentials;
use OpenIDConnect\OAuth2\Grant\Factory;
use PHPUnit\Framework\TestCase;

class ClientCredentialsTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnClientCredentialsWhenRegisterInFactory(): void
    {
        $factory = new Factory();
        $factory->setGrant('some', new ClientCredentials());

        $this->assertInstanceOf(ClientCredentials::class, $factory->getGrant('some'));
    }

    /**
     * @test
     */
    public function shouldReturnClientCredentialsWhenNotRegisterInFactory(): void
    {
        $factory = new Factory();

        $this->assertInstanceOf(ClientCredentials::class, $factory->getGrant('client_credentials'));
    }

    /**
     * @test
     */
    public function shouldReturnOkayWhenRequireParametersIsReady(): void
    {
        $target = (new Factory())->getGrant('client_credentials');

        $actual = $target->prepareRequestParameters([]);

        $this->assertSame('client_credentials', $actual['grant_type']);
    }
}
