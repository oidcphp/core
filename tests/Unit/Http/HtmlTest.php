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
}
