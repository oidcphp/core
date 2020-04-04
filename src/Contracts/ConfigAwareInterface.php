<?php

namespace OpenIDConnect\Contracts;

interface ConfigAwareInterface
{
    /**
     * @param ConfigInterface $config
     * @return mixed
     */
    public function setConfig(ConfigInterface $config);
}
