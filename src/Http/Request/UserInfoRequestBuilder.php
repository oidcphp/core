<?php

declare(strict_types=1);

namespace OpenIDConnect\Http\Request;

use DomainException;
use MilesChou\Psr\Http\Client\HttpClientAwareTrait;
use MilesChou\Psr\Http\Client\HttpClientInterface;
use MilesChou\Psr\Http\Message\PendingRequest;
use OpenIDConnect\Config;
use OpenIDConnect\Exceptions\OAuth2ServerException;
use OpenIDConnect\Traits\ConfigAwareTrait;

/**
 * Generate Request for userinfo endpoint
 *
 * @see https://openid.net/specs/openid-connect-core-1_0.html#UserInfoRequest
 */
class UserInfoRequestBuilder
{
    use ConfigAwareTrait;
    use HttpClientAwareTrait;

    /**
     * @param Config $config
     * @param HttpClientInterface $httpClient
     */
    public function __construct(Config $config, HttpClientInterface $httpClient)
    {
        $this->setConfig($config);
        $this->setHttpClient($httpClient);
    }

    public function build(string $accessToken): PendingRequest
    {
        try {
            $userInfoEndpoint = $this->config->providerMetadata()->require('userinfo_endpoint');
        } catch (DomainException $e) {
            throw new OAuth2ServerException('Provider does not support userinfo_endpoint');
        }

        $request = $this->httpClient->createRequest('GET', $userInfoEndpoint)
            ->withHeader('Authorization', 'Bearer ' . $accessToken);

        return new PendingRequest($request, $this->httpClient);
    }
}
