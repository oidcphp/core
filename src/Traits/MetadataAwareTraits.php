<?php

namespace OpenIDConnect\Traits;

use DomainException;
use OutOfBoundsException;
use RuntimeException;

trait MetadataAwareTraits
{
    /**
     * @var array
     */
    private $metadata;

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
     * @return array
     */
    public function toArray(): array
    {
        return $this->metadata;
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
}
