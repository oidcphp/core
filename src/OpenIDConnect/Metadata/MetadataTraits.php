<?php

namespace OpenIDConnect\Metadata;

use DomainException;
use OutOfBoundsException;
use RuntimeException;

trait MetadataTraits
{
    /**
     * @var array
     */
    private $metadata = [];

    /**
     * @param string $key
     */
    public function assertKey(string $key): void
    {
        if (!array_key_exists($key, $this->metadata)) {
            throw new RuntimeException("{$key} must be configured in metadata");
        }
    }

    /**
     * @param array $keys
     */
    public function assertKeys(array $keys): void
    {
        foreach ($keys as $key) {
            $this->assertKey($key);
        }
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->offsetExists($key);
    }

    public function offsetExists($key)
    {
        return array_key_exists($key, $this->metadata);
    }

    public function offsetGet($key)
    {
        if ($this->offsetExists($key)) {
            return $this->metadata[$key];
        }

        throw new OutOfBoundsException("Key '{$key}' is not found in metadata");
    }

    public function offsetSet($key, $value)
    {
        throw new DomainException('Cannot set any value on metadata instance');
    }

    public function offsetUnset($key)
    {
        throw new DomainException('Cannot unset any value on a metadata instance');
    }

    /**
     * @return array
     * @see \JsonSerializable
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Return a clone object with new value
     *
     * @param string $key
     * @param mixed $value
     * @return static
     */
    public function withMetadata(string $key, $value)
    {
        $clone = clone $this;
        $clone->metadata[$key] = $value;

        return $clone;
    }

    /**
     * @return array
     */
    abstract public function toArray(): array;
}
