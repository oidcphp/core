<?php

declare(strict_types=1);

namespace OpenIDConnect\Traits;

use BadMethodCallException;
use DomainException;

trait ParameterTrait
{
    /**
     * @var array<string>
     */
    protected static array $snakeCache = [];

    /**
     * @var array<mixed>
     */
    protected array $parameters = [];

    /**
     * Convert a string to snake case
     *
     * @link https://github.com/laravel/framework/blob/v6.5.0/src/Illuminate/Support/Str.php#L525-L540
     * @param string $str
     * @return string
     */
    protected static function snake(string $str): string
    {
        $key = $str;

        if (isset(static::$snakeCache[$key])) {
            return static::$snakeCache[$key];
        }

        if (!ctype_lower($key)) {
            $key = (string)preg_replace('/\s+/u', '', ucwords($key));
            $key = (string)preg_replace('/(.)(?=[A-Z])/u', '$1' . '_', $key);
            $key = mb_strtolower($key, 'UTF-8');
        }

        return static::$snakeCache[$key] = $key;
    }

    /**
     * @param string $name
     * @param array<mixed> $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        $key = static::snake($name);

        if ($this->has($key)) {
            return $this->get($key);
        }

        throw new BadMethodCallException("Undefined method '{$name}'");
    }

    /**
     * @param mixed $item
     * @return static
     */
    public function append($item)
    {
        $this->parameters[] = $item;

        return $this;
    }

    /**
     * @param array-key $key
     */
    public function assertHasKey($key): void
    {
        if (!$this->has($key)) {
            throw new DomainException("Missing parameter key: '{$key}'");
        }
    }

    /**
     * @param array<array-key> $keys
     */
    public function assertHasKeys(array $keys): void
    {
        foreach ($keys as $key) {
            $this->assertHasKey($key);
        }
    }

    /**
     * @param array-key $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->parameters[$key] ?? $default;
    }

    /**
     * @param array-key $key
     */
    public function has($key): bool
    {
        return isset($this->parameters[$key]);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * @param array<mixed> $parameters
     * @return static
     */
    public function merge(array $parameters)
    {
        $this->parameters = array_merge($this->parameters, $parameters);

        return $this;
    }

    /**
     * @param array-key $key
     * @return mixed
     */
    public function require($key)
    {
        $this->assertHasKey($key);

        return $this->get($key);
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return $this->parameters;
    }

    /**
     * @param array-key $key
     * @param mixed $value
     * @return static
     */
    public function with($key, $value)
    {
        $clone = clone $this;
        $clone->parameters[$key] = $value;

        return $clone;
    }
}
