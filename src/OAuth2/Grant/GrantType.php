<?php

declare(strict_types=1);

namespace OpenIDConnect\OAuth2\Grant;

use InvalidArgumentException;

/**
 * Represents a type of authorization grant.
 *
 * @see https://tools.ietf.org/html/rfc6749#section-1.3
 */
abstract class GrantType
{
    /**
     * @var string
     */
    protected $grantType;

    /**
     * Required parameters when call token endpoint
     *
     * @var array<string>
     */
    protected $requiredParameters = [];

    /**
     * Prepares the parameters used on token endpoint
     *
     * @param array<mixed> $parameters
     * @return array<mixed>
     */
    public function prepareTokenRequestParameters(array $parameters): array
    {
        // Check the parameters is ready
        foreach (array_merge($this->requiredParameters) as $name) {
            if (!isset($parameters[$name])) {
                throw new InvalidArgumentException("Missing parameter '{$name}'");
            }
        }

        return array_merge([
            'grant_type' => $this->grantType,
        ], $parameters);
    }
}
