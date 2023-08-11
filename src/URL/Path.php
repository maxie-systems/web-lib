<?php

namespace MaxieSystems\URL;

use MaxieSystems\Exception\Messages as EMsg;

/**
 * @property-read string $slug
 */
class Path
{
    final public static function Parse(string $v, ?bool &$absolute, ?bool &$trailing_slash): array
    {
        $absolute = $trailing_slash = false;
        if ('' === $v) {
            return [];
        } elseif ('/' === $v) {
            $absolute = $trailing_slash = true;
            return [];
        }
        if ('/' === $v[0]) {
            $absolute = true;
            $v = substr($v, 1);
        }
        if ('/' === substr($v, -1)) {
            $trailing_slash = true;
            $v = substr($v, 0, -1);
        }
        return explode('/', $v);
    }

    // final public static function FromArray(iterable $d) : Path
     // {
        // $p = new Path(null);
        // foreach($d as $v) $p->segments[] = $v;
        // return $p;
     // }

// http://msse2.maxtheps.beget.tech/?path=classes/URL/url.php - здесь есть тесты для нормализации, которые необходимо реализовать.
// http://msse2.maxtheps.beget.tech/?path=classes/URL-Path/demo.php - демо с большим набором операций.
// http://msse2.maxtheps.beget.tech/?path=url.path.php
// function Normalize(string $path, string $base_path = '/') : string// Он должен изменить текущий объект, или создать новый??? Сделать две версии метода: для "себя" и для нового объекта??? Для "себя" нормализацию можно выполнять в конструкторе, передавая туда флаг, показывающий, что требуется нормализация.
// {
    // if('' === $path) $path = $base_path;
    // elseif('\\' === $path) $path = '/';// Вот это, по идее, актуально только для файловых директорий.
    // elseif('/' !== $path)
     // {
        // do
         // {
            // $path = str_replace('//', '/', $path, $count);
         // }
        // while($count);
        // $path = \MaxieSystems\URL::Encode($path);
        // if('/' !== $path[0]) $path = namespace\Relative2Root($path, $base_path);
        // $path = namespace\RemoveDots($path);// Relative2Root вроде бы вызывает RemoveDots ??? Убрать повторяющийся вызов !!!
     // }
    // return $path;
// }

    final public function __construct(string $value)//, bool $readonly = false)
    {
        $this->segments = new Path\Segments($value, function (string $segment, int $i, int $last_i): ?string {
            if ('' === $segment && $i > 0 && $i < $last_i) {
                return null;
            }
            return $segment;
        });
        return;
        if ('' === $value) {
            $this->segments = new Path\Segments('');
        } else {
            // $value = \MaxieSystems\URL::Encode($value);
            $s = $this->Parse($value, $this->absolute, $this->trailing_slash);
            // filter, normalize, encode ???
        }
        //$this->value = $value;
    }

    final public function endsWith(string $dir, string &$sub = null): bool
    {
        if ('' === $dir) {
            throw new \UnexpectedValueException('First argument must not be empty');
        }
        if ('/' === $dir) {
            return '' === $this->segments[-1];
        }
        // to-do...
        return false;
    }

    final public function startsWith(string $dir, string &$sub = null): bool
    {
        if ('' === $dir) {
            throw new \UnexpectedValueException('First argument must not be empty');
        }
        if ('/' === $dir) {
            return '' === $this->segments[0];
        }
        // to-do...
        return false;
    }
    // final public function IsSubdirOf(string $dir, Path &$sub = null) : bool// нужно изменить алгоритм его работы.
     // {
        // $sub = null;
        // if('' === $dir || '/' === $dir) return false;// if($this->InvalidStr($dir) || $this->InvalidStr($this->value);
        // $dir = trim($dir, '/');
        // $res = false;
        // $this->Walk(function(Path $p0, Path $p1, int $n, int $count, string $dir, int $len) use(&$res, &$sub){
            // $path = "$p0";
            // if($path === $dir)
             // {
                // $sub = $p1;
                // if($count === $n);# пути равны
                // else $res = true;
                // return false;
             // }
            // elseif(strlen($path) > $len) return false;
        // }, false, $dir, strlen($dir));
        // return $res;
     // }
//Traverse(callable $callback, int $mode, ...$args) ??? mode - набор констант, пока что их будет 2. А какой тогда будет смысл возвращаемого значения?
    // final public function Walk(callable $callback, bool $reverse = false, ...$args) : ?int
     // {
        // $cnt = count($this->segments);
        // if(!$cnt) return null;
        // $p0 = new Path();
        // $s0 = $p0->__get('segments');
        // $p1 = self::FromArray($this->segments);
        // $s1 = $p1->__get('segments');
        // if($reverse)
         // {
            // $n = $cnt;
            // do
             // {
                // if(false === $callback($p1, $p0, $n, $cnt, ...$args))
                 // {
                    // if(count($s1) && $this->absolute)
                     // {
                        // $p1->absolute = true;
                        // $p1->OnModify();
                     // }
                    // if(count($s0)) $p0->__set('trailing_slash', $this->trailing_slash);
                    // elseif(count($s1)) $p1->__set('trailing_slash', $this->trailing_slash);
                    // return $n;
                 // }
                // $s0->Unshift($s1->Pop());
                // --$n;
             // }
            // while(count($s1));
         // }
        // else
         // {
            // $n = 0;
            // do
             // {
                // $s0[] = $s1->Shift();
                // if(false === $callback($p0, $p1, ++$n, $cnt, ...$args))
                 // {
                    // if(count($s1)) $p1->__set('trailing_slash', $this->trailing_slash);
                    // elseif(count($s0)) $p0->__set('trailing_slash', $this->trailing_slash);
                    // if(count($s0) && $this->absolute)
                     // {
                        // $p0->absolute = true;
                        // $p0->OnModify();
                     // }
                    // return $n;
                 // }
             // }
            // while(count($s1));
         // }
        // if($this->absolute) $p0->absolute = true;
        // $p0->__set('trailing_slash', $this->trailing_slash);
        // return null;
     // }

    final public function __get($name)
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
        throw new \Error(EMsg::undefinedProperty($this, $name));
    }

    // final public function __set($name, $value)
     // {
        // if('trailing_slash' === $name)
         // {
            // $this->$name = (bool)$value;
            // $this->Modify();
            // return;
         // }
        // list($e, $m, $c) = E\UndefinedProperty($this, $name);
        // throw new $e($m, $c);
     // }

    final public function __toString()
    {
        return $this->segments->__toString();
    }

    final public function __debugInfo(): array
    {
        return ['value' => $this->__toString()];// 'decoded' => htmlspecialchars(rawurldecode($v))
    }

    final public function __clone()
    {
        $this->segments = clone $this->segments;
    }

    private Path\Segments $segments;
    private $absolute = false;
    private $trailing_slash = false;
}
