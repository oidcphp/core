<?php

declare(strict_types=1);

namespace OpenIDConnect\Metadata;

use OpenIDConnect\Contracts\ClientMetadataInterface;

/**
 * @see \OpenIDConnect\Contracts\ClientMetadataAwareInterface
 */
trait ClientMetadataAwareTrait
{
    /**
     * @var ClientMetadataInterface
     */
    protected $clientMetadata;

    public function clientMetadata(): ClientMetadataInterface
    {
        return $this->clientMetadata;
    }

    public function getClientMetadata(string $name, $default = null)
    {
        return $this->clientMetadata->get($name, $default);
    }

    public function requireClientMetadata(string $name)
    {
        return $this->clientMetadata->require($name);
    }

    public function setClientMetadata(ClientMetadataInterface $clientMetadata)
    {
        $this->clientMetadata = $clientMetadata;

        return $this;
    }
}
