<?php

namespace Tests\Unit\OAuth2\Grant;

use OpenIDConnect\OAuth2\Grant\ClientCredentials;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ClientCredentialsTest extends TestCase
{
    #[Test]
    public function shouldReturnOkayWhenRequireParametersIsReady(): void
    {
        $target = new ClientCredentials();

        $actual = $target->prepareTokenRequestParameters([]);

        $this->assertSame('client_credentials', $actual['grant_type']);
    }
}
