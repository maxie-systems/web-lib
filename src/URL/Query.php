<?php

namespace MaxieSystems\URL;

class Query implements \Iterator, \Countable, \ArrayAccess, \jsonSerializable
{
    final public static function deleteParams(string &$q, string ...$params): int
    {
        $params = array_fill_keys($params, 1);
        $s = '';
        $i = 0;
        foreach (explode('&', $q) as $sp) {
            $p = explode('=', $sp, 2);
            $n = urldecode($p[0]);// \PHP_QUERY_RFC1738
            $pos0 = strpos($n, '[');
            if (false !== $pos0) {
                $pos1 = strpos($n, ']', $pos0 + 1);
                if (false !== $pos1) {
                    $n = substr($n, 0, $pos0);
                }
            }
            if (isset($params[$n])) {
                ++$i;
            } else {
                if ('' !== $s) {
                    $s .= '&';
                }
                $s .= $sp;
            }
        }
        if ($i) {
            $q = $s;
        }
        return $i;
    }

    final public function __construct(string|iterable $q)
    {
        if (is_string($q)) {
            if ('' !== $q) {
                parse_str($q, $this->query);
            }
        } else {
            foreach ($q as $k => $v) {
                $this->__set($k, $v);
            }
        }
    }

    final public function count(): int
    {
        return $this->query ? count($this->toArray()) : 0;
    }

    final public function toArray(): array
    {
        return array_filter($this->query, function ($v): bool {
            return $v !== null;
        });
    }

    final public function current(): mixed
    {
        return current($this->query);
    }

    final public function next(): void
    {
        next($this->query);
    }

    final public function valid(): bool
    {
        return null !== key($this->query);
    }

    final public function rewind(): void
    {
        reset($this->query);
    }

    final public function key(): mixed
    {
        return key($this->query);
    }

    final public function offsetExists($offset): bool
    {
        return $this->__isset($offset);
    }

    final public function offsetUnset($offset): void
    {
        $this->__unset($offset);
    }

    final public function &offsetGet($offset): mixed
    {
        return $this->__get($offset);
    }

    final public function __debugInfo(): array
    {
        return $this->toArray();
    }

    final public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    final public function __isset($name): bool
    {
        return isset($this->query[$name]);
    }

    final public function &__get($name): mixed
    {
        if (isset($this->query[$name])) {
            return $this->query[$name];
        }
        $a = null;
        return $a;
    }

    final public function __set($name, $value): void
    {
        if (null === $name) {
            throw new \UnexpectedValueException('Parameter name cannot be null');
        }
        if (null === $value) {
            $this->__unset($name);
        } else {
            $this->query[$name] = $value;
        }
    }

    final public function copy(string|iterable|\stdClass $q, string ...$names)
    {
        if (is_string($q)) {
            $query = $q;
            $q = [];
            parse_str($query, $q);
            if ($names) {
                foreach ($names as $k) {
                    if (isset($q[$k])) {
                        $this->__set($k, $q[$k]);
                    }
                }
                return;
            }
        } else {
            if ($names) {
                $names = array_fill_keys($names, 1);
                foreach ($q as $k => $v) {
                    if (isset($names[$k])) {
                        $this->__set($k, $v);
                    }
                }
                return;
            }
        }
        foreach ($q as $k => $v) {
            $this->__set($k, $v);
        }
    }

    final public function delete(string ...$names): array
    {
        if ($names) {
            $deleted = [];
            foreach ($names as $name) {
                if (isset($this->query[$name])) {
                    $deleted[$name] = $this->query[$name];
                    unset($this->query[$name]);
                }
            }
        } else {
            $deleted = $this->query;
            $this->query = [];
        }
        return $deleted;
    }

    final public function deleteAllExcept(string $name, string ...$names): array
    {
        $names[] = $name;
        $names = array_fill_keys($names, 1);
        $deleted = [];
        foreach ($this->query as $name => $v) {
            if (!isset($names[$name])) {
                $deleted[$name] = $this->query[$name];
                unset($this->query[$name]);
            }
        }
        return $deleted;
    }

    final public function offsetSet($offset, $value): void
    {
        $this->__set($offset, $value);
    }

    final public function __unset(string|int $name): void
    {
        if (array_key_exists($name, $this->query)) {
            unset($this->query[$name]);
        }
    }

    final public function ksort(): self
    {
        ksort($this->query, SORT_STRING);
        return $this;
    }

    final public function __toString()
    {
        return http_build_query($this->query);
    }

    private array $query = [];
}
