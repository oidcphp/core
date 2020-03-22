<?php

declare(strict_types=1);

namespace OpenIDConnect\OAuth2\Builder;

use DomainException;
use OpenIDConnect\OAuth2\Exceptions\OAuth2ServerException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

class AuthorizationFormResponseBuilder
{
    use BuilderTrait;

    /**
     * @param array<mixed> $parameters
     * @return ResponseInterface
     */
    public function build(array $parameters): ResponseInterface
    {
        /** @var ResponseFactoryInterface $responseFactory */
        $responseFactory = $this->container->get(ResponseFactoryInterface::class);

        /** @var StreamFactoryInterface $streamFactory */
        $streamFactory = $this->container->get(StreamFactoryInterface::class);

        return $responseFactory->createResponse()
            ->withBody($streamFactory->createStream($this->generateForm($parameters)));
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
