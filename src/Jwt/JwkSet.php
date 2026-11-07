<?php

declare(strict_types=1);

namespace OpenIDConnect\Jwt;

use InvalidArgumentException;

/**
 * @link https://www.rfc-editor.org/rfc/rfc7517.html#section-5
 */
class JwkSet
{
    /**
     * @var array<array<string, mixed>>
     */
    private $keys = [];

    /**
     * @param array<string, mixed> $jwkSet
     */
    public function __construct(array $jwkSet = [])
    {
        $this->init($jwkSet);
    }

    /**
     * @param array<string, mixed> $jwkSet
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
     * @param array<string, mixed> $jwk
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
     * @return array<string, mixed>
     */
    public function get($index): array
    {
        if (!$this->has($index)) {
            throw new InvalidArgumentException('Undefined index');
        }

        return $this->keys[$index];
    }

    /**
     * @return array{keys: list<array<string, mixed>>}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Transfer to array
     *
     * @return array{keys: list<array<string, mixed>>}
     */
    public function toArray(): array
    {
        return ['keys' => array_values($this->keys)];
    }
}
