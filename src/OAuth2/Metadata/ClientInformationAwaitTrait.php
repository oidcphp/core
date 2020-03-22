<?php

declare(strict_types=1);

namespace OpenIDConnect\OAuth2\Metadata;

trait ClientInformationAwaitTrait
{
    /**
     * @var ClientInformation
     */
    protected $clientInformation;

    /**
     * @param ClientInformation $clientInformation
     * @return static
     */
    public function setClientInformation(ClientInformation $clientInformation)
    {
        $this->clientInformation = $clientInformation;

        return $this;
    }
}
