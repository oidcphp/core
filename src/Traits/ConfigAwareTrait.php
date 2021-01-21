<?php

declare(strict_types=1);

namespace OpenIDConnect\Traits;

use OpenIDConnect\Config;

trait ConfigAwareTrait
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Config $config
     * @return static
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }
}
