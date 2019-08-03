<?php

namespace OpenIDConnect;

use ArrayAccess;
use BadMethodCallException;
use Illuminate\Support\Collection;
use OutOfBoundsException;

/**
 * @see https://openid.net/specs/openid-connect-discovery-1_0.html#ProviderMetadata
 */
class ProviderMetadata implements ArrayAccess
{
    public const REQUIRED_METADATA = [
        'authorization_endpoint',
        'id_token_signing_alg_values_supported',
        'issuer',
        'jwks_uri',
        'response_types_supported',
        'subject_types_supported',
        'token_endpoint',
    ];

    /**
     * @var Collection
     */
    private $metadata;

    /**
     * @param array $metadata
     * @return static
     */
    public static function create(array $metadata = [])
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
    public function authorizationEndpoint(): string
    {
        return $this->metadata['authorization_endpoint'];
    }

    /**
     * @return array
     */
    public function idTokenSigningAlgValuesSupported(): array
    {
        return $this->metadata['id_token_signing_alg_values_supported'];
    }

    /**
     * @return string
     */
    public function issuer(): string
    {
        return $this->metadata['issuer'];
    }

    /**
     * @return string
     */
    public function jwksUri(): string
    {
        return $this->metadata['jwks_uri'];
    }

    /**
     * @return array
     */
    public function responseTypesSupported(): array
    {
        return $this->metadata['response_types_supported'];
    }

    /**
     * @return array
     */
    public function subjectTypesSupported(): array
    {
        return $this->metadata['subject_types_supported'];
    }

    /**
     * @return string
     */
    public function tokenEndpoint(): string
    {
        return $this->metadata['token_endpoint'];
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
