<?php

namespace MaxieSystems\HTTP;

class Header implements IHeader
{
    final public function __construct(string $name, string $value)
    {
        $this->value = $value;
        if ('' === $name) {
            $this->header = $value;
            $this->lc_name = $this->name = '';
        } else {
            list($this->name, $this->_name, $this->lc_name) = self::GetIndexNames($name);
            $this->header = "$this->name: $value";
        }
    }

    final public static function IsMultiple(string $lc_name): bool
    {
        static $names = [
            'set-cookie' => 1,
            'Set-Cookie' => 1,
        ];
        return isset($names[$lc_name]);
    }

    final public static function FromString(string $header): ?Header
    {
        $value = explode(':', $header, 2);
        if (2 === count($value)) {
            $name = $value[0];
            $value = ltrim($value[1]);
        } elseif ('HTTP/' === substr($value[0], 0, 5)) {
            $name = '';
            $value = $value[0];
        } else {
            return null;
        }
        return new self($name, $value);
    }

    final public static function GetIndexNames(string $name): array
    {
        $lc_name = strtolower($name);
        return [ucwords($name, '-'), str_replace('-', '_', $lc_name), $lc_name];
    }

    final public static function ParseContentType(string $content_type): array
    {
        $r = ['', ''];
        if ($content_type) {
            $a = explode(';', $content_type, 2);
            $r[0] = strtolower($a[0]);
            if (!empty($a[1])) {
                $s = 'charset=';
                if (false !== ($pos = strpos($a[1], $s))) {
                    $r[1] = strtolower(trim(substr($a[1], $pos + strlen($s)), ' \'"'));
                }
            }
        }
        return $r;
    }

    final public function __get($name)
    {
        static $names = ['name' => 1, '_name' => 1, 'lc_name' => 1, 'value' => 1];
        if (isset($names[$name])) {
            return $this->$name;
        }
        throw new \Error('Undefined property: ' . __CLASS__ . "::$$name");
    }

    final public function GetNames(): array
    {
        return [$this->name, $this->_name, $this->lc_name];
    }
    final public function __toString()
    {
        return $this->header;
    }
    final public function __debugInfo(): array
    {
        return ['header' => $this->header];
    }
    final public function __clone()
    {
    }

    private $name;
    private $_name;
    private $lc_name;
    private $value;
    private $header;
}
