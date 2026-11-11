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

    /**
     * The escaping of every character is tested in HtmlTest
     */
    #[Test]
    public function shouldEscapeTheActionAndTheParametersOfLogoutForm(): void
    {
        $config = $this->createConfig([
            'end_session_endpoint' => 'https://somewhere/logout?"><script>alert(1)</script>',
        ]);
        $target = new InitiateLogoutFormPostResponseBuilder($config, new MockClient());

        $actual = (string)$target->build([
            'post_logout_redirect_uri' => '"/><script>alert(document.cookie)</script>',
            '"><script>' => 'a',
        ])->getBody();

        $this->assertStringNotContainsString('<script>', $actual);
        $this->assertStringContainsString('action="https://somewhere/logout?&quot;&gt;&lt;script&gt;', $actual);
        $this->assertStringContainsString('name="post_logout_redirect_uri" value="&quot;/&gt;&lt;script&gt;', $actual);
        $this->assertStringContainsString('name="&quot;&gt;&lt;script&gt;" value="a"', $actual);
    }
}
