<?php

namespace OpenIDConnect\OAuth2\Grant;

trait GrantFactoryAwareTrait
{
    /**
     * @var GrantFactory
     */
    protected $grantFactory;

    /**
     * @param GrantFactory $grantFactory
     * @return static
     */
    public function setGrantFactory(GrantFactory $grantFactory)
    {
        $this->grantFactory = $grantFactory;

        return $this;
    }
}
