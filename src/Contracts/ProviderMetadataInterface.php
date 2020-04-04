<?php

namespace OpenIDConnect\Contracts;

interface ProviderMetadataInterface extends Parameterable
{
    /**
     * @return JwkSetInterface
     */
    public function jwkSet(): JwkSetInterface;
}
