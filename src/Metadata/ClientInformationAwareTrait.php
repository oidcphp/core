<?php

declare(strict_types=1);

namespace OpenIDConnect\Metadata;

trait ClientInformationAwareTrait
{
    /**
     * @var ClientInformation
     */
    protected $clientInformation;

    /**
     * @param ClientInformation $clientInformation
     * @return $this
     */
    public function setClientInformation(ClientInformation $clientInformation): self
    {
        $this->clientInformation = $clientInformation;

        return $this;
    }
}
