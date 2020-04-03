<?php

declare(strict_types=1);

namespace OpenIDConnect\Http\Request;

use DomainException;
use MilesChou\Psr\Http\Message\PendingRequest;
use OpenIDConnect\Exceptions\OAuth2ServerException;
use OpenIDConnect\Http\Builder;

/**
 * Generate Request for userinfo endpoint
 *
 * @see https://openid.net/specs/openid-connect-core-1_0.html#UserInfoRequest
 */
class UserInfoRequestBuilder extends Builder
{
    public function build(string $accessToken): PendingRequest
    {
        try {
            $userInfoEndpoint = $this->providerMetadata->require('userinfo_endpoint');
        } catch (DomainException $e) {
            throw new OAuth2ServerException('Provider does not support userinfo_endpoint');
        }

        $request = $this->httpFactory->createRequest('GET', $userInfoEndpoint)
            ->withHeader('Authorization', 'Bearer ' . $accessToken);

        return new PendingRequest($request, $this->httpClient);
    }
}
