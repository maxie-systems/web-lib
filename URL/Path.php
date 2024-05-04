<?php

namespace MaxieSystems\URL;

use MaxieSystems\EMessages;

/**
 * @property-read string $slug
 */
class Path
{
    final public function __construct(string $value, string $base_path = '')
    {
        //$this->absolute = true;
        //if ('' === $base_path) {
        //} elseif ('' === $base_path) {
        //}
        if ('' === $value) {
            if ('' === $base_path) {
                $this->segments = new Path\Segments('');
                $this->absolute = false;
                $this->ends_with_slash = false;
                return;
            }
            #$value = $base_path;
        //} elseif ('/' === $value) {
        }
        $value = \MaxieSystems\URL::mergePaths($base_path, $value);
        $this->segments = new Path\Segments(
            $value,
            function (string $segment, int $i, int $last_i): ?string {
                $is_empty = ('' === $segment);
                if ($i === 0) {
                    $this->absolute = $is_empty;
                    if ($last_i === 0) {
                        $this->ends_with_slash = $this->absolute;
                    }
                    if ($this->absolute) {
                        $segment = null;
                    }
                } elseif ($i === $last_i) {
                    $this->ends_with_slash = $is_empty;
                    if ($this->ends_with_slash) {
                        $segment = null;
                    }
                } elseif ($is_empty) {
                    $segment = null;
                }
                return $segment;
            }
        );
        // \MaxieSystems\URL::encode($segment);
        // filter, normalize, encode ???
    }

    final public function endsWith(string $dir, string &$sub = null): bool
    {
        $sub = null;
        if ('' === $dir) {
            throw new \UnexpectedValueException('First argument must not be empty');
        }
        if ('/' === $dir) {
            return $this->ends_with_slash;
        }
        // to-do...
        return false;
    }

    final public function startsWith(string $dir, string &$sub = null): bool
    {
        $sub = null;
        if ('' === $dir) {
            throw new \UnexpectedValueException('First argument must not be empty');
        }
        if ('/' === $dir) {
            return $this->absolute;
        }
        // to-do...
        return false;
    }

    final public function isAbsolute(): bool
    {
        return $this->absolute;
    }

// final public function decode or getDecoded(): string{ return rawurldecode($v);return $this->segments->getDecoded(); }
/*    final public function __get($name)
    {
        static $keys = ['segments' => 1, 'absolute' => 1, 'trailing_slash' => 1];// trailing_slash = is_dir ???
        if (isset($keys[$name])) {
            return $this->$name;
        } elseif ('slug' === $name) {
            return $this->segments[-1];# то же самое, что basename
        } elseif ('decoded' === $name) {
            return rawurldecode($this->__toString());
        } elseif ('extension' === $name) {// А что делать с такими именами: dollysites.tar.gz ???
            $s = $this->__get('slug');
            return '' === "$s" ? '' : pathinfo($s, PATHINFO_EXTENSION);
        }
        // elseif('dirname' === $name) return '\\' === ($v = dirname($this->value)) ? '/' : $v;
        throw new \Error(EMessages::undefinedProperty($this, $name));
    }*/

    final public function __set($name, $value): void
    {
        throw new \Error(EMessages::readonlyObject($this));
    }

    final public function __unset($name): void
    {
        throw new \Error(EMessages::readonlyObject($this));
    }

    final public function __toString()
    {
        $s = $this->absolute ? '/' : '';
        $s .= $this->segments->__toString();
        if ($this->ends_with_slash && count($this->segments)) {
            $s .= '/';
        }
        return $s;
    }

    final public function __debugInfo(): array
    {
        return ['value' => $this->__toString()];
    }

    final public function __clone()
    {
        $this->segments = clone $this->segments;
    }

    private readonly Path\Segments $segments;
    private readonly bool $absolute;
    private readonly bool $ends_with_slash;
}
