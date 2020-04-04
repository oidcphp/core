<?php

namespace OpenIDConnect\Contracts;

use DomainException;
use JsonSerializable;

interface Parameterable extends JsonSerializable
{
    /**
     * @param mixed $item
     * @return $this
     */
    public function append($item);

    /**
     * @param string|int $key
     */
    public function assertHasKey($key): void;

    /**
     * @param array<string> $keys
     */
    public function assertHasKeys(array $keys): void;

    /**
     * @param string|int $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * @param string|int $key
     * @return bool
     */
    public function has($key): bool;

    /**
     * @param array $parameters
     * @return $this
     */
    public function merge(array $parameters);

    /**
     * @param string|int $key
     * @return mixed
     * @throws DomainException
     */
    public function require($key);

    /**
     * @return array<mixed>
     */
    public function toArray(): array;

    /**
     * Return a clone object with new value
     *
     * @param string|int $key
     * @param mixed $value
     * @return $this
     */
    public function with($key, $value);
}
