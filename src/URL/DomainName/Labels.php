<?php

namespace MaxieSystems\URL\DomainName;

use MaxieSystems\Exception\Messages as EMsg;

class Labels implements \ArrayAccess, \Countable
{
    final public function __construct(string $value)
    {
        if ('' === $value) {
            $this->labels = [];
        } else {
            $value = explode('.', $value);
            if ('' === end($value)) {
                array_pop($value);
            }
            $this->labels = $value;
        }
    }

    final public function offsetGet($i): ?string
    {
        if (is_int($i)) {
            $j = $this->convertOffset($i);
            return $this->labels[$j] ?? null;
        }
        throw new \TypeError(EMsg::illegalOffsetType($i));
    }

    final public function offsetSet($i, $v): void
    {
        throw new \Error(EMsg::readonlyObject($this));
    }

    final public function offsetUnset($i): void
    {
        throw new \Error(EMsg::readonlyObject($this));
    }

    final public function toArray(): array
    {
        $r = [];
        $cnt = count($this->labels);
        for ($i = $cnt; $i > 0; --$i) {
            $r[$i] = $this->labels[$cnt - $i];
        }
        return $r;
    }

    final public function __debugInfo(): array
    {
        return $this->toArray();
    }

    final public function offsetExists($i): bool
    {
        return is_int($i) ? isset($this->labels[$this->convertOffset($i)]) : false;
    }

    final public function __toString()
    {
        return implode('.', $this->labels);
    }

    final public function count(): int
    {
        return count($this->labels);
    }

    final protected function convertOffset(int $k): int
    {
        return $k < 0 ? -1 - $k : count($this->labels) - $k;
    }

    private readonly array $labels;
}
