<?php

declare(strict_types=1);

namespace OpenIDConnect\Http;

/**
 * HTML helpers for the responses built by the library
 *
 * @internal
 */
final class Html
{
    /**
     * Escape a value for an HTML text or a quoted attribute value
     *
     * `ENT_SUBSTITUTE` replaces invalid UTF-8 instead of returning an empty string.
     *
     * @param string $value
     * @return string
     */
    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    }
}
