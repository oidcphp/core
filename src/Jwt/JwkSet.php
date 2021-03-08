<?php

declare(strict_types=1);

namespace OpenIDConnect\Jwt;

use InvalidArgumentException;

/**
 * @see https://tools.ietf.org/html/rfc7517#section-5
 */
class JwkSet
{
    /**
     * @var array
     */
    private $keys = [];

    /**
     * @param array $jwkSet
     */
    public function __construct(array $jwkSet = [])
    {
        $this->init($jwkSet);
    }

    /**
     * @param array $jwkSet
     * @return JwkSet
     */
    public function init(array $jwkSet): JwkSet
    {
        if (isset($jwkSet['keys'])) {
            foreach ($jwkSet['keys'] as $key) {
                $this->add($key);
            }
        }

        return $this;
    }

    /**
     * @param array $jwk
     * @return JwkSet
     */
    public function add(array $jwk): JwkSet
    {
        if (isset($jwk['kid'])) {
            $this->keys[$jwk['kid']] = $jwk;
        } else {
            $this->keys[] = $jwk;
        }

        return $this;
    }

    /**
     * @param int|string $index
     * @return bool
     */
    public function has($index): bool
    {
        return array_key_exists($index, $this->keys);
    }

    /**
     * @param int|string $index
     * @return array<mixed>
     */
    public function get($index): array
    {
        if (!$this->has($index)) {
            throw new InvalidArgumentException('Undefined index');
        }

        return $this->keys[$index];
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Transfer to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return ['keys' => array_values($this->keys)];
    }
}
