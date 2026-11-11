<?php

namespace Tests\Unit\Http\Response;

use MilesChou\Psr\Http\Client\Testing\MockClient;
use MilesChou\Psr\Http\Message\Testing\TestResponse;
use OpenIDConnect\Http\Response\AuthorizationFormPostResponseBuilder;
use OpenIDConnect\Http\Response\InitiateLogoutFormPostResponseBuilder;
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
            ->assertSee('name="bar" value="b"')
            ->assertHeader('Content-Type', 'text/html; charset=utf-8')
            ->assertHeader('Cache-Control', 'no-store');
    }

    #[Test]
    public function shouldEscapeParameterValues(): void
    {
        $target = new AuthorizationFormPostResponseBuilder($this->createConfig(), new MockClient());

        $actual = (string)$target->build([
            'login_hint' => '"/><script>alert(document.cookie)</script>',
            'ui_locales' => "a&b 'c'",
        ])->getBody();

        $this->assertStringNotContainsString('<script>', $actual);
        $this->assertStringContainsString(
            'name="login_hint" value="&quot;/&gt;&lt;script&gt;alert(document.cookie)&lt;/script&gt;"',
            $actual
        );
        $this->assertStringContainsString('name="ui_locales" value="a&amp;b &apos;c&apos;"', $actual);
    }

    #[Test]
    public function shouldEscapeParameterNames(): void
    {
        $target = new AuthorizationFormPostResponseBuilder($this->createConfig(), new MockClient());

        $actual = (string)$target->build([
            '"><script>alert(1)</script>' => 'a',
        ])->getBody();

        $this->assertStringNotContainsString('<script>', $actual);
        $this->assertStringContainsString('name="&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;" value="a"', $actual);
    }

    #[Test]
    public function shouldEscapeActionUri(): void
    {
        $config = $this->createConfig([
            'authorization_endpoint' => 'https://somewhere/auth?a=1&b="><script>alert(1)</script>',
        ]);
        $target = new AuthorizationFormPostResponseBuilder($config, new MockClient());

        $actual = (string)$target->build(['foo' => 'a'])->getBody();

        $this->assertStringNotContainsString('<script>', $actual);
        $this->assertStringContainsString(
            'action="https://somewhere/auth?a=1&amp;b=&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;"',
            $actual
        );
    }

    #[Test]
    public function shouldFlattenArrayValuesLikeTheRedirectQuery(): void
    {
        $config = $this->createConfig([
            'end_session_endpoint' => 'https://somewhere/logout',
        ]);
        $target = new InitiateLogoutFormPostResponseBuilder($config, new MockClient());

        $actual = (string)$target->build([
            'scope' => ['openid', 'profile'],
        ])->getBody();

        $this->assertStringNotContainsString('value="Array"', $actual);
        $this->assertStringContainsString('name="scope[0]" value="openid"', $actual);
        $this->assertStringContainsString('name="scope[1]" value="profile"', $actual);
    }
}
