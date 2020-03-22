<?php

namespace Tests\OAuth2\Grant;

use OpenIDConnect\OAuth2\Grant\ClientCredentials;
use PHPUnit\Framework\TestCase;

class ClientCredentialsTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnOkayWhenRequireParametersIsReady(): void
    {
        $target = new ClientCredentials();

        $actual = $target->prepareTokenRequestParameters([]);

        $this->assertSame('client_credentials', $actual['grant_type']);
    }
}
