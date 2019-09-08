<?php

namespace OpenIDConnect\OAuth2\Grant;

use OpenIDConnect\Exceptions\RelyingPartyException;

/**
 * Represents a type of authorization grant.
 *
 * @see https://tools.ietf.org/html/rfc6749#section-1.3
 */
abstract class AbstractGrant
{
    /**
     * Returns the name of this grant, eg. 'grant_name', which is used as the
     * grant type when encoding URL query parameters.
     *
     * @return string
     */
    abstract protected function getName(): string;

    /**
     * Returns a list of all required request parameters.
     *
     * @return array
     */
    abstract protected function getRequiredRequestParameters(): array;

    /**
     * Returns this grant's name as its string representation. This allows for
     * string interpolation when building URL query parameters.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Prepares an access token request's parameters by checking that all
     * required parameters are set, then merging with any given defaults.
     *
     * @param array $options
     * @return array
     */
    public function prepareRequestParameters(array $options): array
    {
        $required = $this->getRequiredRequestParameters();
        $provided = array_merge([
            'grant_type' => $this->getName(),
        ], $options);

        foreach ($required as $name) {
            if (!isset($provided[$name])) {
                throw new RelyingPartyException(sprintf(
                    'Required parameter not passed: "%s"',
                    $name
                ));
            }
        }

        return $provided;
    }
}
