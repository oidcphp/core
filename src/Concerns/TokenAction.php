<?php

namespace OpenIDConnect\Concerns;

use OpenIDConnect\Exceptions\OAuth2ServerException;
use OpenIDConnect\Http\Request\TokenRequestBuilder;
use OpenIDConnect\OAuth2\Grant\AuthorizationCode;
use OpenIDConnect\OAuth2\Grant\GrantType;
use Psr\Http\Client\ClientExceptionInterface;

trait TokenAction
{
    /**
     * @param array $parameters
     * @param array $checks
     * @param GrantType|null $grant Default is AuthorizationCode.
     * @return array
     */
    public function token(array $parameters = [], array $checks = [], ?GrantType $grant = null): array
    {
        $grant = $grant ?? new AuthorizationCode();

        $request = (new TokenRequestBuilder($this->config, $this->httpClient))
            ->setClientAuthentication($this->clientAuthentication)
            ->build(array_merge($parameters, $checks), $grant);

        try {
            $response = $request->send();
        } catch (ClientExceptionInterface $e) {
            $msg = 'Server error: ' . $e->getMessage();
            throw new OAuth2ServerException($msg, 0, $e);
        }

        return $this->parseResponse($response);
    }
}
