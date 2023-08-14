<?php

namespace MaxieSystems\URL;

use ArrayAccess;
use MaxieSystems\Exception\URL\InvalidHostException;
use MaxieSystems\Exception\URL\InvalidSchemeException;
use MaxieSystems\URLReadOnly;
use MaxieSystems\URL as URLWritable;

class Resolver
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

    /**
     * Resolve URL to its absolute form
     *
     * @param \MaxieSystems\URL $url
     * @return \MaxieSystems\URL
     */
    final public function __invoke(URLWritable $url): void
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
}
