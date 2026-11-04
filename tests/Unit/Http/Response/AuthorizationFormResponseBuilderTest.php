<?php

namespace Tests\Unit\Http\Response;

use MilesChou\Psr\Http\Client\Testing\MockClient;
use MilesChou\Psr\Http\Message\Testing\TestResponse;
use OpenIDConnect\Http\Response\AuthorizationFormPostResponseBuilder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthorizationFormResponseBuilderTest extends TestCase
{
    #[Test]
    public function shouldReturnAuthorizationHtmlWhenCallBuild(): void
    {
        $target = new AuthorizationFormPostResponseBuilder($this->createConfig(), new MockClient());

        $actual = $target->build([
            'foo' => 'a',
            'bar' => 'b',
        ]);

        TestResponse::fromBaseResponse($actual)
            ->assertSee('action="https://somewhere/auth"')
            ->assertSee('name="foo" value="a"')
            ->assertSee('name="bar" value="b"');
    }
}
