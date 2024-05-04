<?php

namespace MaxieSystems;

/**
 * Parse URLs
 *
 * @property-read string $scheme
 * @property-read string|\Stringable $host
 * @property-read int|string $port
 * @property-read string $user
 * @property-read string $pass
 * @property-read string|\Stringable $path
 * @property-read string|\Stringable $query
 * @property-read string $fragment
 */
class URLReadOnly implements URLInterface
{
    public function __construct(string|array|object $source, callable $on_create = null, ...$args)
    {
        $url = new URL($source);
        if (null !== $on_create) {
            $on_create($url, ...$args);
        }
        $this->onCreate($url);
        $this->url = $url;
    }

    protected function onCreate(URL $url): void
    {
        # This method is a hook which can be redefined in the subclasses.
    }

    final public function isEmpty(string $group = null): bool
    {
        return $this->url->isEmpty($group);
    }

    final public function isAbsolute(URLType &$type = null): bool
    {
        return $this->url->isAbsolute($type);
    }

    final public function getType(): URLType
    {
        return $this->url->getType();
    }

    final public function __unset($name): void
    {
        throw new \Error(EMessages::readonlyObject($this));
    }

    final public function __set($name, $value): void
    {
        throw new \Error(EMessages::readonlyObject($this));
    }

    public function __isset($name): bool
    {
        return isset($this->url->$name);
    }

    public function __get($name): mixed
    {
        return $this->url->$name;
    }

    final public function offsetExists(mixed $offset): bool
    {
        return $this->__isset($offset);
    }

    final public function offsetGet(mixed $offset): mixed
    {
        return $this->__get($offset);
    }

    final public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->__set($offset, $value);
    }

    final public function offsetUnset(mixed $offset): void
    {
        $this->__unset($offset);
    }

    public function current(): mixed
    {
        return $this->url->current();
    }

    final public function next(): void
    {
        $this->url->next();
    }

    #[\ReturnTypeWillChange]
    final public function key(): string
    {
        return $this->url->key();
    }

    final public function valid(): bool
    {
        return $this->url->valid();
    }

    final public function rewind(): void
    {
        $this->url->rewind();
    }

    final public function __toString()
    {
        return $this->url->__toString();
    }

    public function __clone()
    {
        $this->url = clone $this->url;
    }

    public function __debugInfo(): array
    {
        return $this->url->__debugInfo();
    }

    final public function toStdClass(callable $callback = null, ...$args): \stdClass
    {
        return $this->url->toStdClass($callback);
    }

    final public function toArray(callable $callback = null, ...$args): array
    {
        return $this->url->toArray($callback);
    }

    private readonly URL $url;
}
