<?php

declare(strict_types=1);

namespace OpenIDConnect\Traits;

use OpenIDConnect\Contracts\ConfigInterface;

trait ConfigAwareTrait
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @param ConfigInterface $config
     * @return $this
     */
    public function setConfig(ConfigInterface $config): self
    {
        $this->config = $config;
        return $this;
    }
}
