<?php

declare(strict_types=1);

namespace OpenIDConnect\Token;

use OpenIDConnect\Config;
use OpenIDConnect\Contracts\TokenFactoryInterface;
use OpenIDConnect\Contracts\TokenSetInterface;
use OpenIDConnect\Traits\ConfigAwareTrait;

class TokenFactory implements TokenFactoryInterface
{
    use ConfigAwareTrait;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->setConfig($config);
    }

    public function create(array $parameters, $clockTolerance = 10): TokenSetInterface
    {
        return new TokenSet($this->config, $parameters, $clockTolerance);
    }
}
