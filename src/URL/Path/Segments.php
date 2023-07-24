<?php

namespace MaxieSystems\URL\Path;

use MaxieSystems\Exception\Messages as EMsg;

class Segments implements \Countable, \ArrayAccess
{
    final public function __construct(string|iterable $path, callable $filter_segment = null, ...$args)
    {
        if (is_string($path)) {
            if ('' === $path || '/' === $path) {
                $this->segments = [];
            } else {
                $this->segments = $this->filterSegments(explode('/', $path), $filter_segment, ...$args);
            }
        } else {
            $this->segments = $this->filterSegments($path, $filter_segment, ...$args);
        }
    }

    final public static function filterSegmentRaw(string $segment, int $i, int $last_i): ?string
    {
        return '' === $segment && (0 === $i || $i === $last_i) ? null : $segment;
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
}
