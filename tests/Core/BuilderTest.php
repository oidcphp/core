<?php

namespace Tests\Core;

use OpenIDConnect\Core\Builder;
use OpenIDConnect\Core\Client;
use Tests\TestCase;

class BuilderTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnClient(): void
    {
        $builder = Builder::create($this->createProviderMetadata(), $this->createClientInformation())
            ->useDefaultContainer();

        $this->assertInstanceOf(Client::class, $builder->createOpenIDConnectClient());
    }
}
