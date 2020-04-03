<?php

declare(strict_types=1);

namespace OpenIDConnect\Http\Response;

use DomainException;
use OpenIDConnect\Exceptions\OAuth2ServerException;
use OpenIDConnect\Http\Builder;
use Psr\Http\Message\ResponseInterface;

class AuthorizationFormResponseBuilder extends Builder
{
    /**
     * @param array<mixed> $parameters
     * @return ResponseInterface
     */
    public function build(array $parameters): ResponseInterface
    {
        return $this->httpFactory->createResponse()
            ->withBody($this->httpFactory->createStream($this->generateForm($parameters)));
    }

    /**
     * @param array<mixed> $parameters
     * @return string
     */
    private function generateForm(array $parameters): string
    {
        try {
            return $this->generateHtml($this->providerMetadata->require('authorization_endpoint'), $parameters);
        } catch (DomainException $e) {
            throw new OAuth2ServerException('Provider does not support authorization_endpoint');
        }
    }

    /**
     * @param string $url
     * @param array<mixed> $parameters
     * @return string
     */
    private function generateHtml(string $url, array $parameters): string
    {
        $formInput = implode('', array_map(function ($value, $key) {
            return "<input type=\"hidden\" name=\"{$key}\" value=\"{$value}\"/>";
        }, $parameters, array_keys($parameters)));

        return <<< HTML
<!DOCTYPE html>
<head><title>Requesting Authorization</title></head>
<body onload="javascript:document.forms[0].submit()">
<form method="post" action="{$url}">{$formInput}</form>
</body>
</html>
HTML;
    }
}
