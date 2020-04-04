<?php

declare(strict_types=1);

namespace OpenIDConnect\Metadata;

use OpenIDConnect\Contracts\ClientMetadataInterface;

trait ClientMetadataAwareTrait
{
    /**
     * @var ClientMetadataInterface
     */
    protected $clientMetadata;

    /**
     * @return ClientMetadataInterface
     */
    public function clientMetadata(): ClientMetadataInterface
    {
        return $this->clientMetadata;
    }

    /**
     * @param ClientMetadataInterface $clientMetadata
     * @return $this
     */
    public function setClientMetadata(ClientMetadataInterface $clientMetadata)
    {
        $this->clientMetadata = $clientMetadata;

        return $this;
    }
}
