<?php

namespace MaxieSystems;

use MaxieSystems\Exception\Messages as EMsg;

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
        $s = '';
        if ('' !== $url->scheme) {
            $s .= "$url->scheme:";
        }
        $h = "$url->host";
        if ('' !== $h) {
            $s .= '//';
            if ('' !== $url->user) {
                $s .= $url->user;
                if ($url->pass) {
                    $s .= ":$url->pass";
                }
                $s .= '@';
            }
            $s .= $h;
            if ($url->port) {
                $s .= ":$url->port";
            }
        }
        $s .= $url->path;
        $q = "$url->query";
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

    final public static function isComponentName(string $name): bool
    {
        return isset(self::$components[$name]);
    }

    # $filter_component: function(string $name, mixed $value, \Closure $get_src_val, ...$args): mixed
    public function __construct(string|array|object $source, callable $filter_component = null, ...$args)
    {
        $is_object = false;
        if (is_string($source)) {
            if ('' === $source || '#' === $source) {
                $source = [];
            } else {
                $u = parse_url($source);
                if (!$u) {
                    throw new \UnexpectedValueException('Invalid URL');
                }
                /** @var array $source */
                $source = $u;
            }
        } elseif (is_array($source) || ($source instanceof \ArrayAccess)) {
        } else {
            $is_object = true;
        }
        $get_src_val = $is_object ?
            function (string $name, $default = null) use ($source): mixed {
                return $source->$name ?? $default;
            }
            : function (string $name, $default = null) use (&$source): mixed {
                return $source[$name] ?? $default;
            };
        if (null === $filter_component) {
            $filter = [$this, 'filterComponent'];
        } else {
            $filter = function (string $k, $v) use (&$filter_component, $get_src_val, &$args): mixed {
                $v = $filter_component($k, $v, $get_src_val, ...$args);
                return $this->filterComponent($k, $v);
            };
        }
        foreach ($this->data as $k => $v) {
            if (null !== ($v = $filter($k, $get_src_val($k, $v)))) {
                $this->data[$k] = $v;
            }
        }
    }

    final public function isAbsolute(URLType &$type = null): bool
    {
        if ('' === (string)$this->scheme) {
            if ('' === (string)$this->host) {
                $p = (string)$this->path;
                $type = ('' === $p || '/' !== $p[0]) ? URLType::Relative : URLType::RootRelative;
                return false;
            } else {
                $type = URLType::ProtocolRelative;
            }
        } else {
            $type = URLType::Absolute;
        }
        return true;
    }

    final public function getType(): URLType
    {
        $this->isAbsolute($type);
        return $type;
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
        $this->isAbsolute($r['type']);
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

    private array $data = [
        'scheme' => '', 'host' => '', 'port' => '', 'user' => '', 'pass' => '',
        'path' => '', 'query' => '', 'fragment' => ''
    ];

    private static array $components = [
        'scheme' => ['type' => 'string'], 'host' => ['type' => 'mixed'], 'port' => ['type' => 'int'],
        'user' => ['type' => 'string'], 'pass' => ['type' => 'string'],
        'path' => ['type' => 'mixed'], 'query' => ['type' => 'mixed'], 'fragment' => ['type' => 'string']
    ];
}
