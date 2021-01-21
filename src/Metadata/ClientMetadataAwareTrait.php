<?php

declare(strict_types=1);

namespace OpenIDConnect\Metadata;

trait ClientMetadataAwareTrait
{
    /**
     * @var ClientMetadata
     */
    protected $clientMetadata;

    public function clientMetadata(): ClientMetadata
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

    public function setClientMetadata(ClientMetadata $clientMetadata)
    {
        $this->clientMetadata = $clientMetadata;

        return $this;
    }
}
