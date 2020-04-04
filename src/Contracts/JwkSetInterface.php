<?php

declare(strict_types=1);

namespace OpenIDConnect\Contracts;

use JsonSerializable;
use OpenIDConnect\Jwt\JwkSet;

interface JwkSetInterface extends JsonSerializable
{
    /**
     * @param array<mixed> $jwkSet
     * @return JwkSetInterface
     */
    public function init(array $jwkSet): JwkSetInterface;

    /**
     * @param array<mixed> $jwk
     * @return JwkSetInterface
     */
    public function add(array $jwk): JwkSetInterface;

    /**
     * @param int|string $index
     * @return bool
     */
    public function has($index): bool;

    /**
     * @param int|string $index
     * @return array<mixed>
     */
    public function get($index): array;

    /**
     * Transfer to array
     *
     * @return array<mixed>
     */
    public function toArray(): array;
}
