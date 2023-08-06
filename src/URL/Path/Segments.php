<?php

namespace MaxieSystems\URL\Path;

use MaxieSystems\Exception\Messages as EMsg;

class Segments implements \Countable, \ArrayAccess
{
    final public function __construct(string|iterable $path, callable $filter_segment = null, ...$args)
    {
        if (null === $filter_segment) {
            $this->filter_segment = null;
        } elseif ($filter_segment instanceof \Closure) {
            $this->filter_segment = $filter_segment;
        } else {
            $this->filter_segment = \Closure::fromCallable($filter_segment);
        }
        $this->filter_segment_args = $args;
        $this->segments = $this->pathToArray($path);
    }

    final public static function filterSegmentRaw(string $segment, int $i, int $last_i): ?string
    {
        return '' === $segment && (0 === $i || $i === $last_i) ? null : $segment;
    }

    final public static function noFilter(string $s): string
    {
        return $s;
    }

    protected function filterSegment(string $segment, int $i, int $last_i): ?string
    {
        return '' === $segment ? null : $segment;
    }

    final protected function filterSegments(iterable $segments, ?callable $filter_segment, ...$args): array
    {
        if (null === $filter_segment) {
            $filter_segment = [$this, 'filterSegment'];
        }
        $s = [];
        $i = 0;
        $last_i = count($segments) - 1;
        foreach ($segments as $v) {
            $v = $filter_segment($v, $i, $last_i, ...$args);
            if (null !== $v) {
                $s[] = $v;
            }
            ++$i;
        }
        return $s;
    }

    final protected function pathToArray(string|iterable $path): array
    {
        if (is_string($path)) {
            if ('' === $path) {
                return [];
            } else {
                $path = explode('/', $path);
            }
        }
        return $this->filterSegments($path, $this->filter_segment, ...$this->filter_segment_args);
    }

    final public function slice(int $start, ?int $length = null): self
    {
        $start = $this->convertOffset($start);
        if (null === $length) {
            $new = array_slice($this->segments, $start);
        } else {
            $new = array_slice($this->segments, $start, $length);
        }
        return new self($new, [$this::class, 'noFilter']);
    }

    final public function split(int $index, bool $exclude = false): array
    {
        $index = $this->convertOffset($index);
        $a = [[]];
        $i = 0;
        foreach ($this->segments as $k => $v) {
            if ($k === $index) {
                $a[++$i] = [];
                if ($exclude) {
                    continue;
                }
            }
            $a[$i][] = $v;
        }
        foreach ($a as $k => $v) {
            $a[$k] = new self($a[$k], [$this::class, 'noFilter']);
        }
        return $a;
    }

    final public function startsWith(string|iterable $path, string &$sub = null): bool
    {
        $sub = null;
        $c0 = count($this);
        if (0 === $c0) {
            return false;
        }
        $a = $this->pathToArray($path);
        $c1 = count($a);
        if (0 < $c1 && $c1 <= $c0) {
            $tmp = $this->segments;
            foreach ($a as $i => $v) {
                if ($this->segments[$i] === $v) {
                    unset($tmp[$i]);
                } else {
                    return false;
                }
            }
            $sub = $tmp ? implode('/', $tmp) : '';
            return true;
        }
        return false;
    }

    final public function count(): int
    {
        return count($this->segments);
    }

    final public function offsetExists($k): bool
    {
        return isset($this->segments[$this->convertOffset($k)]);
    }

    #[\ReturnTypeWillChange]
    final public function offsetGet($k): ?string
    {
        $k = $this->convertOffset($k);
        return isset($this->segments[$k]) ? $this->segments[$k] : null;
    }

    final public function offsetUnset($k): void
    {
        throw new \Error(EMsg::readonlyObject($this));
    }

    final public function offsetSet($k, $v): void
    {
        throw new \Error(EMsg::readonlyObject($this));
    }

    final public function __toString()
    {
        return implode('/', $this->segments);
    }

    final public function __debugInfo(): array
    {
        return ['value' => $this->__toString(), 'count' => $this->count()];
    }

    final protected function convertOffset(int $k, bool &$neg = null): int
    {
        if ($neg = ($k < 0)) {
            $k += count($this->segments);
        }
        return $k;
    }

    protected readonly array $segments;
    private readonly ?\Closure $filter_segment;
    private readonly array $filter_segment_args;
}
