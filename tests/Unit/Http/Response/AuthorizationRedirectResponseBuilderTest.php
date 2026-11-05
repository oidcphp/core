<?php

namespace Tests\Unit\Http\Response;

use MilesChou\Psr\Http\Client\Testing\MockClient;
use MilesChou\Psr\Http\Message\Testing\TestResponse;
use OpenIDConnect\Http\Response\AuthorizationRedirectResponseBuilder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthorizationRedirectResponseBuilderTest extends TestCase
{
    #[Test]
    public function shouldReturnRedirectResponseWhenCallBuild(): void
    {
        $target = new AuthorizationRedirectResponseBuilder($this->createConfig(), new MockClient());

        $actual = $target->build([
            'foo' => 'a',
            'bar' => 'b',
        ]);

        TestResponse::fromBaseResponse($actual)
            ->assertRedirect('https://somewhere/auth')
            ->assertRedirect('foo=a')
            ->assertRedirect('bar=b');
    }
}
