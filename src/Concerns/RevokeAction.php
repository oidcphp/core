<?php

namespace OpenIDConnect\Concerns;

use OpenIDConnect\Exceptions\OAuth2ServerException;
use OpenIDConnect\Http\Request\RevokeRequestBuilder;
use Psr\Http\Client\ClientExceptionInterface;

trait RevokeAction
{
    public function revoke(string $token, array $parameters = []): void
    {
        $request = (new RevokeRequestBuilder($this->config, $this->httpClient))
            ->setClientAuthentication($this->clientAuthentication)
            ->build(array_merge($parameters, [
                'token' => $token,
            ]));

        try {
            $response = $request->send();
        } catch (ClientExceptionInterface $e) {
            $msg = 'Server error: ' . $e->getMessage();

            throw new OAuth2ServerException($msg);
        }

        $body = json_decode((string)$response->getBody(), true);

        if (isset($body['error'])) {
            throw new OAuth2ServerException('Revocation endpoint return error: ' . $body['error']);
        }
    }
}
