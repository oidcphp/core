<?php

declare(strict_types=1);

namespace OpenIDConnect\Token;

use OpenIDConnect\Contracts\ConfigInterface;
use OpenIDConnect\Contracts\TokenFactoryInterface;
use OpenIDConnect\Contracts\TokenSetInterface;
use OpenIDConnect\Traits\ConfigAwareTrait;

class TokenFactory implements TokenFactoryInterface
{
    use ConfigAwareTrait;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->setConfig($config);
    }

    public function create(array $parameters): TokenSetInterface
    {
        return new TokenSet($this->config, $parameters);
    }
}
