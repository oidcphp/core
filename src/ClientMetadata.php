<?php

namespace OpenIDConnect;

use ArrayAccess;
use BadMethodCallException;
use Illuminate\Support\Collection;
use OutOfBoundsException;

/**
 * The metadata of client registration
 *
 * @see https://tools.ietf.org/html/rfc6749#section-2
 */
class ClientMetadata implements ArrayAccess
{
    public const REQUIRED_METADATA = [
        'client_id',
        'client_secret',
    ];

    /**
     * @var Collection
     */
    private $metadata;

    /**
     * @param array $metadata
     * @return static
     */
    public static function create(array $metadata)
    {
        return new static($metadata);
    }

    /**
     * @param array $metadata
     */
    public function __construct(array $metadata = [])
    {
        $this->metadata = collect($metadata);

        if (!$this->metadata->has(self::REQUIRED_METADATA)) {
            throw new OutOfBoundsException('Required config is missing. Config: ' . $this->metadata->toJson());
        }
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return $this->metadata['client_id'];
    }

    /**
     * @return string
     */
    public function secret(): string
    {
        return $this->metadata['client_secret'];
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
        throw new BadMethodCallException('Cannot set any value on a ProviderMetadata instance');
    }

    public function offsetUnset($key)
    {
        throw new BadMethodCallException('Cannot unset any value on a ProviderMetadata instance');
    }
}
