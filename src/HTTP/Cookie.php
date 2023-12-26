<?php

namespace MaxieSystems\HTTP;

class Cookie implements \Iterator, \JsonSerializable // ???
{
    final public static function ArrayToHeader(array $a): string
    {
        $s = '';
        foreach ($a as $k => $v) {
            $s .= ('' === $s ? '' : '; ') . $k . '=' . urlencode($v);
        }
        return $s;
    }

    final public function __construct(string $s)
    {
        $idx = [
            'Expires' => (object)['name' => 'expires', 'hdlr' => 1],
            'Max-Age' => (object)['name' => 'max_age', 'hdlr' => 1],
            'Path' => (object)['name' => 'path', 'hdlr' => 1],
            'Domain' => (object)['name' => 'domain', 'hdlr' => 1],
            'Secure' => (object)['name' => 'secure', 'hdlr' => false],
            'HttpOnly' => (object)['name' => 'httponly', 'hdlr' => false],
        ];
        foreach (explode(';', $s) as $i => $v) {
            $v = explode('=', trim($v), 2);
            if ($i) {
                foreach ($idx as $j => $k) {
                    if (0 === strcasecmp($v[0], $j)) {
                        $this->data[$k->name] = false === $k->hdlr ? true : $v[1];
                        unset($idx[$j]);
                        break;
                    }
                }
            } elseif (2 === count($v)) {
                $this->data['name'] = $v[0];
                $this->data['value'] = urldecode($v[1]);
            } else # invalid cookie without =
             {
                $this->data['name'] = '';
                $this->data['value'] = urldecode($v[0]);
            }
        }
        foreach ($idx as $k) {
            if (null === $this->data[$k->name]) {
                if (false === $k->hdlr) {
                    $this->data[$k->name] = false;
                }
            } else {
                throw new \Exception();
            }
        }
    }

    final public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        throw new \Error('Undefined property: ' . __CLASS__ . '::$' . $name);
    }

    // final public function __isset($name) {}
    final public function rewind()
    {
        reset($this->data);
    }
    final public function next()
    {
        next($this->data);
    }
    final public function current()
    {
        return current($this->data);
    }
    final public function valid()
    {
        return null !== key($this->data);
    }
    final public function key()
    {
        return key($this->data);
    }
    final public function jsonSerialize()
    {
        return ;
    }
    final public function __debugInfo()
    {
        return $this->data;
    }

    private $data = ['name' => null, 'value' => null, 'expires' => null, 'max_age' => null, 'path' => null, 'domain' => null, 'secure' => null, 'httponly' => null];
}
