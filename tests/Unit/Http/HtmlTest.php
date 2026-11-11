<?php

namespace Tests\Unit\Http;

use OpenIDConnect\Http\Html;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HtmlTest extends TestCase
{
    public static function escapeCases(): iterable
    {
        yield 'plain' => ['abc', 'abc'];
        yield 'double quote' => ['"', '&quot;'];
        yield 'single quote' => ["'", '&apos;'];
        yield 'angle brackets' => ['<script>', '&lt;script&gt;'];
        yield 'ampersand' => ['a&b', 'a&amp;b'];
        yield 'invalid UTF-8' => ["a\xFFb", "a\u{FFFD}b"];
    }

    #[Test]
    #[DataProvider('escapeCases')]
    public function shouldEscapeValue(string $value, string $expected): void
    {
        $this->assertSame($expected, Html::escape($value));
    }

    #[Test]
    public function shouldBuildFormPostWithEscapedActionAndFields(): void
    {
        $actual = Html::formPost('https://somewhere/auth?a=1&b="x"', [
            'login_hint' => '"/><script>',
            '"name"' => 'a',
        ]);

        $this->assertStringContainsString('<form method="post" action="https://somewhere/auth?a=1&amp;b=&quot;x&quot;">', $actual);
        $this->assertStringContainsString('<input type="hidden" name="login_hint" value="&quot;/&gt;&lt;script&gt;"/>', $actual);
        $this->assertStringContainsString('<input type="hidden" name="&quot;name&quot;" value="a"/>', $actual);
        $this->assertStringNotContainsString('<script>', $actual);
    }

    #[Test]
    public function shouldBuildFormPostFieldsLikeTheRedirectQuery(): void
    {
        $actual = Html::formPost('https://somewhere/auth', [
            'scope' => ['openid', 'profile'],
            'skipped' => null,
            'flag' => false,
        ]);

        $this->assertStringContainsString('name="scope[0]" value="openid"', $actual);
        $this->assertStringContainsString('name="scope[1]" value="profile"', $actual);
        $this->assertStringContainsString('name="flag" value="0"', $actual);
        $this->assertStringNotContainsString('skipped', $actual);
    }

    #[Test]
    public function shouldBuildFormPostWithoutFields(): void
    {
        $actual = Html::formPost('https://somewhere/auth');

        $this->assertStringContainsString('<form method="post" action="https://somewhere/auth"></form>', $actual);
    }
}
