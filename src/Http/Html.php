<?php

declare(strict_types=1);

namespace OpenIDConnect\Http;

use function array_map;
use function htmlspecialchars;
use function implode;

use const ENT_HTML5;
use const ENT_QUOTES;
use const ENT_SUBSTITUTE;

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

    /**
     * HTML page that posts the parameters to the action URI as soon as it is loaded
     *
     * The browser submits the hidden inputs as an application/x-www-form-urlencoded body, so the
     * inputs are built from the same name and value pairs as the query of the redirect. The names,
     * the values and the action URI are escaped, since they can come from the application or from
     * the provider metadata.
     *
     * @link https://openid.net/specs/openid-connect-core-1_0.html#FormSerialization
     *
     * @param string $action
     * @param array<string, mixed> $parameters
     * @return string
     */
    public static function formPost(string $action, array $parameters = []): string
    {
        $formInput = implode('', array_map(static function (array $field): string {
            return '<input type="hidden" name="' . self::escape($field[0])
                . '" value="' . self::escape($field[1]) . '"/>';
        }, Query::buildPairs($parameters)));

        $action = self::escape($action);

        return <<< HTML
<!DOCTYPE html>
<html>
<head><title>Requesting Authorization</title></head>
<body onload="javascript:document.forms[0].submit()">
<form method="post" action="{$action}">{$formInput}</form>
</body>
</html>
HTML;
    }
}
