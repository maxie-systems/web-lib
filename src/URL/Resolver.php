<?php

namespace MaxieSystems\URL;

use ArrayAccess;
use MaxieSystems\Exception\URL\InvalidHostException;
use MaxieSystems\Exception\URL\InvalidSchemeException;
use MaxieSystems\URLInterface;
use MaxieSystems\URLReadOnly;
use MaxieSystems\URL as URLWritable;
use MaxieSystems\URLType;
use Stringable;

class Resolver implements FilterComponentInterface
{
    public function __construct(public readonly URLReadOnly $base)
    {
        if ('' === $base->scheme) {
            throw new InvalidSchemeException('Base URL: scheme undefined');
        }
        if ('' === (string)$base->host) {
            throw new InvalidHostException('Base URL: host undefined');
        }
    }

    final public function __invoke(
        string $name,
        mixed $value,
        array|ArrayAccess $src_url,
        ...$args
    ): string|Stringable|int|null {
        return match ($name) {
            'scheme' => $this->handleScheme($value),
            'host' => $this->handleHost($value, $src_url),
            'fragment' => $this->handleFragment($this->{$this->invoke}($name, $value)),
            default => $this->{$this->invoke}($name, $value)
        };
    }

    final public function getLastFilteredURLType(): URLType
    {
        if ($this->filtering) {
            throw new \Error('URL must not be accessed before initialization');
        }
        return $this->filtered_url_type;
    }

    /**
     * Resolve URL to its absolute form
     *
     * @param \MaxieSystems\URL $url
     * @return \MaxieSystems\URL
     */
    final public function resolve(URLWritable $url): void
    {
        # Transform References - https://datatracker.ietf.org/doc/html/rfc3986#section-5.2.2
        if ('' === $url->scheme) {
            if ($url->isEmpty()) {
                $url->copy($this->base);
                return;
            }
            $url->scheme = $this->base->scheme;
            if ($url->isEmpty('authority')) {
                $url->copy($this->base, 'authority');
                $url->path = URLWritable::pathToAbsolute($this->base->path, $url->path, $path_type);
                if ($path_type === PathType::Empty) {
                    if ('' === (string)$url->query) {
                        $url->query = $this->base->query;
                    }
                } elseif ($path_type === PathType::Absolute) {
                    # ::pathToAbsolute() всегда вызывает ::removeDotSegments()
                    # $url->path = URLWritable::removeDotSegments($url->path);
                }
            } else {
                $url->path = URLWritable::removeDotSegments($url->path);
            }
        } else {
            $url->path = URLWritable::removeDotSegments($url->path);
        }
    }

    public function __debugInfo()
    {
        return [];
    }

    private function handleScheme(string $value): string
    {
        # this is the first step so reset object here
        $this->invoke = 'invokeDefault';
        $this->filtering = true;
        if ($this->use_base_scheme = '' === $value) {
            return $this->base->scheme;
        } else {
            $this->filtered_url_type = URLType::Absolute;
            return $value;
        }
    }

    private function handleHost(mixed $value, array|ArrayAccess $src_url): string|Stringable
    {
        if ($this->use_base_scheme) {
            if ('' === (string)$value) {
                if ($src_url instanceof URLInterface) {
                    $is_empty = $src_url->isEmpty();
                } else {
                    $is_empty = true;
                    foreach (URLWritable::getComponentNames() as $n) {
                        if (isset($src_url[$n]) && '' !== $src_url[$n]) {
                            $is_empty = false;
                            break;
                        }
                    }
                }
                if ($is_empty) {
                    $this->invoke = 'invokeEmpty';
                    $this->filtered_url_type = URLType::Empty;
                } else {
                    $this->invoke = 'invokeNoAuthority';
                    $this->filtered_url_type = isset($src_url['path'])
                                    && URLWritable::isPathAbsolute($src_url['path'])
                                    ? URLType::RootRelative : URLType::Relative;
                }
                return $this->base->host;
            } else {
                $this->filtered_url_type = URLType::ProtocolRelative;
            }
        }
        return $value;
    }

    private function handleFragment(mixed $value): string|Stringable
    {
        $this->filtering = false;
        return $value;
    }

    private function invokeDefault(string $name, mixed $value): string|Stringable|int|null
    {
        return match ($name) {
            'port', 'user', 'pass', 'query', 'fragment' => $value,
            'path' => URLWritable::removeDotSegments($value)
        };
    }

    private function invokeNoAuthority(string $name, mixed $value): string|Stringable|int|null
    {
        return match ($name) {
            'port', 'user', 'pass' => $this->base->$name,
            'path' => URLWritable::pathToAbsolute($this->base->path, $value, $this->path_type),
            'query' => $this->path_type === PathType::Empty && '' === $value ? $this->base->query : $value,
            'fragment' => $value
        };
    }

    private function invokeEmpty(string $name, mixed $value): string|Stringable|int|null
    {
        return $this->base->$name;
    }

    private string $invoke;
    private bool $use_base_scheme;
    private ?PathType $path_type = null;
    private URLType $filtered_url_type;
    private bool $filtering;
}
