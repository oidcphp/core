<?php

declare(strict_types=1);

namespace OpenIDConnect;

use GuzzleHttp\ClientInterface as HttpClientInterface;
use OpenIDConnect\Metadata\ClientMetadata as ClientMeta;
use OpenIDConnect\Metadata\MetadataAwareTraits;
use OpenIDConnect\Metadata\ProviderMetadata as ProviderMeta;
use OpenIDConnect\Token\TokenSet;
use OpenIDConnect\Traits\HttpClientAwareTrait;

/**
 * Factory for create anything
 */
class Factory
{
    use HttpClientAwareTrait;
    use MetadataAwareTraits;

    /**
     * @param ProviderMeta $provider
     * @param ClientMeta $client
     * @param HttpClientInterface|null $httpClient
     */
    public function __construct(ProviderMeta $provider, ClientMeta $client, HttpClientInterface $httpClient = null)
    {
        $this->setProviderMetadata($provider);
        $this->setClientMetadata($client);

        if (null !== $httpClient) {
            $this->setHttpClient($httpClient);
        }
    }

    /**
     * @param array $collaborators
     * @return Client
     */
    public function createOpenIDConnectClient(array $collaborators = []): Client
    {
        if (empty($collaborators['httpClient'])) {
            $collaborators['httpClient'] = $this->getHttpClient();
        }

        return new Client($this->providerMetadata, $this->clientMetadata, $collaborators);
    }

    /**
     * @param array $parameters
     * @return TokenSet
     */
    public function createTokenSet(array $parameters): TokenSet
    {
        return new TokenSet($parameters, $this->providerMetadata, $this->clientMetadata);
    }
}
