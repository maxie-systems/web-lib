<?php

namespace MaxieSystems;

use MaxieSystems\Exception\Messages as EMsg;
use MaxieSystems\URL\PathType;

/**
 * Parse URLs
 *
 * @property string $scheme
 * @property string|\Stringable $host
 * @property int|string $port
 * @property string $user
 * @property string $pass
 * @property string|\Stringable $path
 * @property string|\Stringable $query
 * @property string $fragment
 */
class URL implements URLInterface
{
    final public static function parse(string $url, bool &$invalid = null): \stdClass
    {
        $u = parse_url($url);
        $r = new \stdClass();
        if ($invalid = false === $u) {
            foreach (self::$components as $k => $v) {
                $r->$k = '';
            }
        } else {
            foreach (self::$components as $k => $v) {
                $r->$k = $u[$k] ?? '';
            }
        }
        return $r;
    }

    final public static function build(object $url): string
    {
        # My implementation slightly differs from this: https://datatracker.ietf.org/doc/html/rfc3986#section-5.3
        $s = '';
        if ('' !== $url->scheme) {
            $s .= "$url->scheme:";
        }
        $host = (string)$url->host;
        $path = (string)$url->path;
        if ('' !== $host) {
            $s .= '//';
            if ('' !== $url->user) {
                $s .= $url->user;
                if ($url->pass) {
                    $s .= ":$url->pass";
                }
                $s .= '@';
            }
            $s .= $host;
            if ($url->port) {
                $s .= ":$url->port";
            }
            if (self::isPathRootless($path)) {
                $s .= '/';
            }
        }
        $s .= $path;
        $q = (string)$url->query;
        if ('' !== $q) {
            $s .= "?$q";
        }
        if ('' !== $url->fragment) {
            $s .= "#$url->fragment";
        }
        return $s;
    }

    final public static function encode(string $string): string
    {
        static $s = [
            '%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D',
            '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D'
        ];
        static $r = [
            '!',   '*',   "'",   "(",   ")",   ";",   ":",   "@",   "&",   "=",
            "+",   "$",   ",",   "/",   "?",   "%",   "#",   "[",   "]"
        ];
        return str_replace($s, $r, rawurlencode($string));
    }

    final public static function addQueryParameters(string $url, string|array $params): string
    {
        if (is_array($params)) {
            $params = http_build_query($params);
        }
        $pos = strpos($url, '?');
        if (false === $pos) {
            $c = '?';
        } elseif ($pos === strlen($url) - 1) {
            $c = '';
        } else {
            $c = '&';
        }
        return $url . $c . $params;
    }

    /**
     * @param string $q
     * @param array $params
     * @param ?int &$count
     * @return string
     */
    final public static function deleteQueryParameters(string $q, array $params, int &$count = null): string
    {
        $count = 0;
        if (!$params) {
            return $q;
        }
        $params = array_fill_keys($params, 1);
        $s = '';
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
                ++$count;
            } else {
                if ('' !== $s) {
                    $s .= '&';
                }
                $s .= $sp;
            }
        }
        return $count ? $s : $q;
    }

    final public static function mergePaths(string $base_path, string $path, PathType &$path_type = null): string
    {
        # Merge Paths - https://datatracker.ietf.org/doc/html/rfc3986#section-5.2.3
        if ('' === $path) {
            $path_type = PathType::Empty;
            return $base_path;
        } elseif ('/' !== $path[0]) {
            $path_type = PathType::Rootless;
            $pos = strrpos($base_path, '/');
            if (false === $pos) {
                return $path;
            } elseif (0 === $pos) {
                # if strrpos returns 0 then $base_path has only 1 slash - the first slash.
                return "/$path";
            } else {
                ++$pos;
                $p = $base_path;
                if ($pos < strlen($base_path)) {
                    if ('..' === substr($p, $pos)) {
                        $p .= '/';
                    } else {
                        $p = substr($p, 0, $pos);
                    }
                }
                return $p . $path;
            }
        }
        $path_type = PathType::Absolute;
        return $path;
    }

    final public static function pathToAbsolute(string $base_path, string $path, PathType &$path_type = null): string
    {
        # We assume that the $base_url is absolute even if it's not preceded by a slash "/".
        if ('' === $base_path) {
            $base_path = '/';
        } elseif ('/' !== $base_path[0]) {
            $base_path = "/$base_path";
        }
        $path = self::mergePaths($base_path, $path, $path_type);
        return self::removeDotSegments($path);
    }

    final public static function removeDotSegments(string|iterable $raw_path): string
    {
        if (is_string($raw_path)) {
            $raw_path = explode('/', $raw_path);
        }
        # explode('/', '') : [0 => ''], explode('/', '.') : [0 => '.'], explode('/', '$') : [0 => '$']
        # explode('/', '/') : [0 => '', 1 => '']
        $is_abs = false;
        $i = 0;
        $path = [];
        foreach ($raw_path as $v) {
            if ('..' === $v) {
                if (count($path)) {
                    if (end($path) === '..') {
                        $path[] = $v;
                    } else {
                        array_pop($path);
                    }
                } else {
                    #$path[] = $v;
                }
            } elseif ('.' === $v) {
                # skip it...
            } elseif ('' === $v) {
                if (0 === $i) {
                    $is_abs = true;
                }
                # skip it...
            } else {
                $path[] = $v;
            }
            ++$i;
        }
        $s = $is_abs && $i > 1 ? '/' : '';
        if (count($path)) {
            $s .= implode('/', $path);
            if ('' === $v || '.' === $v || '..' === $v) {
                $s .= '/';
            }
        }
        return $s;
    }

    final public static function isComponentName(string $name): bool
    {
        return isset(self::$components[$name]);
    }

    final public static function getComponentNames(): array
    {
        return array_keys(self::$components);
    }

    final public static function isPathAbsolute(string $url_path): bool
    {
        return '' !== $url_path && '/' === $url_path[0];
    }

    final public static function isPathRelative(string $url_path, bool &$is_empty = null): bool
    {
        $is_empty = '' === $url_path;
        return $is_empty || '/' !== $url_path[0];
    }

    final public static function isPathRootless(string $url_path): bool
    {
        # path-rootless: https://datatracker.ietf.org/doc/html/rfc3986#section-3.3
        return '' !== $url_path && '/' !== $url_path[0];
    }

    # $filter_component:
    # callable(string $name, mixed $value, array|\ArrayAccess $src_url, ...$args): string|\Stringable|int|null
    public function __construct(string|array|object $source, callable $filter_component = null, ...$args)
    {
        if (is_string($source)) {
            if ('' === $source || '#' === $source) {
                $source = [];
            } else {
                $u = parse_url($source);
                if (!$u) {
                    throw new Exception\URL\InvalidURLException();
                }
                /** @var array $source */
                $source = $u;
            }
        } elseif (is_array($source) || ($source instanceof \ArrayAccess)) {
        } else {
            $source = new ArrayAccessProxy($source);
        }
        if (null === $filter_component) {
            $filter = [$this, 'filterComponent'];
        } else {
            $filter = function (string $k, $v) use (&$filter_component, &$source, &$args): mixed {
                $v = $filter_component($k, $v, $source, ...$args);
                return $this->filterComponent($k, $v);
            };
        }
        foreach ($this->data as $k => $v) {
            if (null !== ($v = $filter($k, $source[$k] ?? $v))) {
                $this->data[$k] = $v;
            }
        }
    }

    final public function isAbsolute(URLType &$type = null): bool
    {
        $type = $this->getType();
        return URLType::Absolute === $type;
    }

    final public function isEmpty(string $group = null): bool
    {
        if (null === $group) {
            foreach ($this as $v) {
                if ('' !== (string)$v) {
                    return false;
                }
            }
        } elseif (isset(self::$component_group[$group])) {
            foreach ($this as $k => $v) {
                if (!isset(self::$component_group[$group][$k])) {
                    continue;
                } elseif ('' !== (string)$v) {
                    return false;
                }
            }
        } else {
            throw new \UnexpectedValueException('Invalid group name');
        }
        return true;
    }

    final public function copy(URLInterface|array|\ArrayAccess $source_url, string ...$components): self
    {
        if ($source_url instanceof URLInterface) {
            $copy = [$this, 'copyFromURLInterface'];
        # } elseif (is_array($source_url) || ($source_url instanceof \ArrayAccess)) {
        } else {
            $copy = [$this, 'copyFromArray'];
        }
        if ($components) {
            foreach ($components as $name) {
                if (self::isComponentName($name)) {
                    $copy($source_url, $name);
                } elseif (isset(self::$component_group[$name])) {
                    foreach (self::$component_group[$name] as $n) {
                        $copy($source_url, $n);
                    }
                } else {
                    throw new \UnexpectedValueException('Invalid component name');
                }
            }
        } else {
            foreach (self::$components as $name => $c) {
                $copy($source_url, $name);
            }
        }
        return $this;
    }

    final public function getType(): URLType
    {
        if ('' === (string)$this->scheme) {
            if ($this->isEmpty('authority')) {
                if (self::isPathRelative($this->path, $is_empty)) {
                    return $is_empty
                        && '' === (string)$this->__get('query')
                        && '' === (string)$this->__get('fragment') ? URLType::Empty : URLType::Relative;
                } else {
                    return URLType::RootRelative;
                }
            } else {
                return URLType::ProtocolRelative;
            }
        } else {
            return URLType::Absolute;
        }
    }

    public function __isset($name): bool
    {
        return self::isComponentName($name);
    }

    public function __get($name): mixed
    {
        if (self::isComponentName($name)) {
            return $this->data[$name];
        }
        throw new \Error(EMsg::undefinedProperty($this, $name));
    }

    final public function __unset($name): void
    {
        $this->__set($name, null);
    }

    public function __set($name, $value): void
    {
        if (self::isComponentName($name)) {
            $this->data[$name] = $this->filterComponent($name, $value) ?? '';
        } else {
            throw new \Error(EMsg::undefinedProperty($this, $name));
        }
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
        $k = key($this->data);
        if (null !== $k) {
            $v = $this->data[$k];
            return 'mixed' === self::$components[$k]['type'] ? (string)$v : $v;
        }
    }

    final public function next(): void
    {
        next($this->data);
    }

    #[\ReturnTypeWillChange]
    final public function key(): string
    {
        return key($this->data);
    }

    final public function valid(): bool
    {
        return null !== key($this->data);
    }

    final public function rewind(): void
    {
        reset($this->data);
    }

    public function __clone()
    {
        foreach ($this->data as $k => $v) {
            if (is_object($v)) {
                $this->data[$k] = clone $v;
            }
        }
    }

    public function __debugInfo(): array
    {
        $r = $this->toArray();
        $r['type'] = $this->getType();
        return $r;
    }

    final public function __toString()
    {
        return self::Build($this);
    }

    # function(string $name, string|int|object $value, self $this_url, ...$args): mixed
    final public function toStdClass(callable $callback = null, ...$args): \stdClass
    {
        $r = new \stdClass();
        $this->traverse(function (string $k, string|int|object $v) use ($r): void {
            $r->$k = $v;
        }, $callback, ...$args);
        return $r;
    }

    # function(string $name, string|int|object $value, self $this_url, ...$args): mixed
    final public function toArray(callable $callback = null, ...$args): array
    {
        $r = [];
        $this->traverse(function (string $k, string|int|object $v) use (&$r): void {
            $r[$k] = $v;
        }, $callback, ...$args);
        return $r;
    }

    protected function filterComponent(string $name, mixed $value): mixed
    {
        return $value;
    }

    final protected function traverse(callable $action, ?callable $callback, ...$args): void
    {
        if (null === $callback) {
            foreach ($this as $k => $v) {
                $action($k, $v);
            }
        } else {
            foreach ($this->data as $k => $v) {
                $action($k, $callback($k, $v, $this, ...$args));
            }
        }
    }

    private function copyFromURLInterface(URLInterface $source_url, string $name): void
    {
        $this->data[$name] = $this->filterComponent($name, $source_url->$name) ?? '';
    }

    private function copyFromArray(array|\ArrayAccess $source_url, string $name): void
    {
        $this->data[$name] = $this->filterComponent($name, $source_url[$name] ?? '') ?? '';
    }

    private array $data = [
        'scheme' => '', 'host' => '', 'port' => '', 'user' => '', 'pass' => '',
        'path' => '', 'query' => '', 'fragment' => ''
    ];

    private static array $components = [
        'scheme' => ['type' => 'string'], 'host' => ['type' => 'mixed'], 'port' => ['type' => 'int'],
        'user' => ['type' => 'string'], 'pass' => ['type' => 'string'],
        'path' => ['type' => 'mixed'], 'query' => ['type' => 'mixed'], 'fragment' => ['type' => 'string']
    ];
    # <authority> - https://datatracker.ietf.org/doc/html/rfc3986#section-3.2
    private static array $component_group = [
        'authority' => ['host' => 'host', 'port' => 'port', 'user' => 'user', 'pass' => 'pass'],
    ];
}
