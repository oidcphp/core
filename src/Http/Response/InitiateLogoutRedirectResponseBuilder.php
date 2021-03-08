<?php

declare(strict_types=1);

namespace OpenIDConnect\Http\Response;

use OpenIDConnect\Http\Builder;
use Psr\Http\Message\ResponseInterface;

/**
 * @see https://openid.net/specs/openid-connect-rpinitiated-1_0.html#RPLogout
 */
class InitiateLogoutRedirectResponseBuilder extends Builder
{
    /**
     * @param array $parameters
     * @return ResponseInterface
     */
    public function build(array $parameters): ResponseInterface
    {
        return $this->httpClient->createResponse(302)
            ->withHeader(
                'Location',
                (string)$this->generateRedirectUriWithProviderConfig('end_session_endpoint', $parameters)
            );
    }
}
