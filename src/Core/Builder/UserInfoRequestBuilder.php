<?php declare(strict_types=1);

namespace OpenIDConnect\Core\Builder;

use DomainException;
use OpenIDConnect\OAuth2\Builder\BuilderTrait;
use OpenIDConnect\OAuth2\Exceptions\OAuth2ServerException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Generate Request for userinfo endpoint
 *
 * @see https://openid.net/specs/openid-connect-core-1_0.html#UserInfoRequest
 */
class UserInfoRequestBuilder
{
    use BuilderTrait;

    public function build(string $accessToken): RequestInterface
    {
        /** @var RequestFactoryInterface $requestFactory */
        $requestFactory = $this->container->get(RequestFactoryInterface::class);

        try {
            $userInfoEndpoint = $this->providerMetadata->require('userinfo_endpoint');
        } catch (DomainException $e) {
            throw new OAuth2ServerException('Provider does not support userinfo_endpoint');
        }

        return $requestFactory->createRequest('GET', $userInfoEndpoint)
            ->withHeader('Authorization', 'Bearer ' . $accessToken);
    }
}
