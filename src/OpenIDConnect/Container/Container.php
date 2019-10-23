<?php

declare(strict_types=1);

namespace OpenIDConnect\Container;

use OpenIDConnect\Exceptions\EntryNotFoundException;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * @var array
     */
    private $instance;

    /**
     * @param array $instance
     */
    public function __construct(array $instance = [])
    {
        $this->instance = $instance;
    }

    public function get($id)
    {
        if ($this->has($id)) {
            return $this->instance[$id];
        }

        throw new EntryNotFoundException("The entry '{$id}' is not found");
    }

    /**
     * Inject instance
     *
     * @param string $id
     * @param mixed $instance
     */
    public function set(string $id, $instance): void
    {
        $this->instance[$id] = $instance;
    }

    public function has($id)
    {
        return isset($this->instance[$id]);
    }
}
