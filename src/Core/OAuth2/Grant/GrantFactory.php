<?php

namespace OpenIDConnect\Core\OAuth2\Grant;

/**
 * Implement the registry singleton pattern
 */
class GrantFactory
{
    /**
     * @var AbstractGrant[]
     */
    private $instance = [];

    /**
     * Returns a grant singleton by name.
     *
     * If the grant has not be registered, a default grant will be loaded.
     *
     * @param string $name
     * @return AbstractGrant
     */
    public function getGrant($name): AbstractGrant
    {
        if (empty($this->instance[$name])) {
            $this->registerDefaultGrant($name);
        }

        return $this->instance[$name];
    }

    /**
     * Defines a grant singleton in the registry.
     *
     * @param string $name
     * @param AbstractGrant $grant
     * @return self
     */
    public function setGrant($name, AbstractGrant $grant): self
    {
        $this->instance[$name] = $grant;

        return $this;
    }

    /**
     * Registers a default grant singleton by name.
     *
     * @param string $name
     */
    protected function registerDefaultGrant($name): void
    {
        $class = __NAMESPACE__ . '\\' . self::normalizeGrant($name);

        $this->setGrant($name, new $class);
    }

    /**
     * PascalCase the grant. E.g: 'authorization_code' becomes 'AuthorizationCode'
     *
     * @param string $name
     * @return string
     */
    private static function normalizeGrant($name): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name)));
    }
}
