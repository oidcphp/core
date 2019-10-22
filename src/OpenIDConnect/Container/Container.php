<?php

declare(strict_types=1);

namespace OpenIDConnect\Container;

use OpenIDConnect\Exceptions\EntryNotFoundException;
use OpenIDConnect\OAuth2\Grant\GrantFactory;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * @var array
     */
    private $instance;

    public static function createDefaultInstance()
    {
        return new static([
            GrantFactory::class => new GrantFactory(),
            \GuzzleHttp\ClientInterface::class => new \GuzzleHttp\Client(),
            \Psr\Http\Message\StreamFactoryInterface::class => new \Http\Factory\Guzzle\StreamFactory(),
            \Psr\Http\Message\ResponseFactoryInterface::class => new \Http\Factory\Guzzle\ResponseFactory(),
            \Psr\Http\Message\RequestFactoryInterface::class => new \Http\Factory\Guzzle\RequestFactory(),
            \Psr\Http\Message\UriFactoryInterface::class => new \Http\Factory\Guzzle\UriFactory(),
        ]);
    }

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
