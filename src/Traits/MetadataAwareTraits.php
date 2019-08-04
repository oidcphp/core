<?php

namespace OpenIDConnect\Traits;

use BadMethodCallException;
use Illuminate\Support\Collection;
use OutOfBoundsException;

trait MetadataAwareTraits
{
    /**
     * @var Collection
     */
    private $metadata;

    /**
     * @param string $key
     */
    public function assertConfiguration(string $key): void
    {
        if (!$this->metadata->has($key)) {
            throw new OutOfBoundsException("{$key} must be configured on the provider");
        }
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->metadata->toArray();
    }

    public function offsetExists($key)
    {
        return $this->metadata->offsetExists($key);
    }

    public function offsetGet($key)
    {
        return $this->metadata->offsetGet($key);
    }

    public function offsetSet($key, $value)
    {
        throw new BadMethodCallException('Cannot set any value on metadata instance');
    }

    public function offsetUnset($key)
    {
        throw new BadMethodCallException('Cannot unset any value on a metadata instance');
    }
}
