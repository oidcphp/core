<?php

namespace Tests\Unit\Http;

use OpenIDConnect\Http\Query;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QueryTest extends TestCase
{
    #[Test]
    public function shouldEncodeUriQueryPerRfc3986(): void
    {
        $this->assertSame(
            'scope=openid%20profile&redirect_uri=https%3A%2F%2Fapp%2Fcb%3Fa%3D1',
            Query::buildUriQuery(['scope' => 'openid profile', 'redirect_uri' => 'https://app/cb?a=1'])
        );
    }

    /**
     * The example of RFC 6749 Appendix B: SPACE, PERCENT SIGN, AMPERSAND, PLUS SIGN, POUND SIGN and EURO SIGN
     */
    #[Test]
    public function shouldEncodeFormBodyAsTheExampleOfRfc6749AppendixB(): void
    {
        $this->assertSame('v=+%25%26%2B%C2%A3%E2%82%AC', Query::buildFormBody(['v' => " %&+\u{00A3}\u{20AC}"]));
    }

    #[Test]
    public function shouldParseTheOutputOfBothBuilders(): void
    {
        $params = ['scope' => 'openid profile', 'login_hint' => 'a+b@example.com'];

        $this->assertSame($params, Query::parse(Query::buildUriQuery($params)));
        $this->assertSame($params, Query::parse(Query::buildFormBody($params)));
    }
}
