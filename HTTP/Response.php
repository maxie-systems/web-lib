<?php

namespace MaxieSystems\HTTP;

/**
 * @property-read int $http_code
 * @property-read int $content_length
 * @property-read string $content_type
 * @property-read string $mime
 * @property-read ?string $charset
 * @property-read \MaxieSystems\URLInterface $url
 * @property-read \MaxieSystems\HTTP\Headers $headers
 * @property-read $cookies
 * @property-read string $headers_source
 */
class Response// implements \JsonSerializable // ???
{
    // final public function jsonSerialize() { return ['data' => $this->data, 'headers' => $this->__get('headers')]; }
    // public static function __set_state($an_array)
    // {
        // $obj = new A;
        // $obj->var1 = $an_array['var1'];
        // $obj->var2 = $an_array['var2'];
        // return $obj;
    // }

    public function __construct(string $result, array $info, array $headers, array $cookies)
    {
        if (isset($info['header_size']) && ($hsize = $info['header_size'])) {
            $this->properties['headers_source'] = substr($result, 0, $hsize);
            $this->content = substr($result, $hsize);
        } else {
            $this->content = $result;
        }
        $this->info = $info;
        $this->properties['headers'] = $headers;
        $this->properties['cookies'] = $cookies;
    }

    final public function IsJSON(&$data, ?bool $associative = null, int $depth = 512, int $flags = 0): bool
    {
        static $ct = ['application/json' => 1, ];
        $data = null;
        if (isset($ct[$this->__get('mime')])) {
            $data = json_decode($this->__toString(), $associative, $depth, $flags);
            return true;
        }
        return false;
    }

    final public function IsXML(?\DOMDocument &$doc, int $options = 0, string $version = '1.0', string $encoding = 'UTF-8'): bool
    {
        static $ct = ['application/xml' => 1, 'text/xml' => 1];
        $doc = null;
        if (isset($ct[$this->__get('mime')])) {
            $doc = new \DOMDocument($version, $encoding);
            return $doc->LoadXML($this->__toString(), $options);
        }
        return false;
    }

    final public function getInfo(string ...$fields): array
    {
        if ($fields) {
            $r = [];
            foreach ($fields as $f) {
                if (array_key_exists($f, $this->info)) {
                    $r[$f] = $this->info[$f];
                }
            }
            return $r;
        } else {
            return $this->info;
        }
    }

    final public function __debugInfo(): array
    {
        $r = [];
        foreach ($this->properties as $k => $v) {
            $r[$k] = $this->__get($k);
        }
        settype($r['url'], 'string');
        return $r;
    }

    public function __toString()
    {
        return "$this->content";
    }

    public function __get(string $n)
    {
        if ('url' === $n) {
            if (null === $this->$n) {
                $this->$n = new \MaxieSystems\URLReadOnly($this->info[$n]);
            }
            return $this->$n;
        } elseif ('content_length' === $n) {
            return strlen($this->__toString());
        } elseif ('headers' === $n) {
            if (null === $this->$n) {
                $this->$n = new Headers($this->properties[$n]);
            }
            return $this->$n;
        } elseif ('cookies' === $n) {
            return $this->properties[$n];
        } elseif ('mime' === $n || 'charset' === $n) {
            if (null === $this->properties[$n]) {
                $this->InitMIME($n);
            }
            return $this->properties[$n];
        } elseif (isset($this->properties[$n])) {
            return true === $this->properties[$n] ? $this->info[$n] : $this->properties[$n];
        } else {
            throw new \Error(\MaxieSystems\Exception\Messages::undefinedProperty($this, $n));
        }
    }

    final protected function InitMIME(string $name): ?string
    {
        static $nn = ['mime', 'charset'];
        $v = '';
        foreach (Header::ParseContentType($this->__get('content_type')) as $i => $n) {
            $this->properties[$nn[$i]] = $n;
            if ($name === $nn[$i]) {
                $v = $n;
            }
        }
        return $v;
    }

    private $url = null, $headers = null, $cookies = null, $content;
    private readonly array $info;
    private array $properties = ['url' => true, 'http_code' => true, 'content_type' => true, 'content_length' => true,
     'mime' => null, 'charset' => null, 'headers' => null, 'cookies' => null, 'headers_source' => null];
}
