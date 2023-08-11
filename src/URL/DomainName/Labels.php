<?php

namespace MaxieSystems\URL\DomainName;

class Labels implements \ArrayAccess, \Countable
{
    final public static function Check(string $label): string
    {
        if ('' === $label) {
            throw new \UnexpectedValueException('Domain label is empty');
        } elseif ('.' === $label) {
            throw new \UnexpectedValueException('Domain label is invalid: ' . var_export($label, true));
        }
        return $label;
    }

    final public function __construct(string $v)
    {
        if ('' === $v) {
            $this->value = [];
        } else {
            $this->value = explode('.', $v);
            if ('' === end($this->value)) {
                array_pop($this->value);
            }
        }
    }

    final public function offsetGet($i)
    {
        if (is_int($i)) {
            $j = $this->ConvertOffset($i);
            return isset($this->value[$j]) ? $this->value[$j] : null;# Решено не выбрасывать исключение при отсутствующем числовом индексе; предполагался вызов E\UndefinedIndex().
        }
        list($e, $m, $c) = E\InvalidIndex($i);
        throw new $e($m, $c);
    }

// специальный метод(ы) с проверками: Insert(?int $index, $) или Replace ??? Add ???
// если передаётся объект, массив, другое - проверять типы?
// если я могу удалять только последний элемент (и не могу остальные произвольно), то почему можно произвольно задавать любой элемент таким вызовом $labels[2] = 'my-site'; или даже $labels[1] = 'co.uk'?
// А как заменить co.uk на com?
// Насколько интуитивно понятна запись $labels[] = 'static-5'???
    // final public function offsetSet($i, $v)
     // {//throw new \TypeError('Argument 1 passed to '.__METHOD__.'() must be iterable or be an instance of stdClass or be of the type string, '.gettype($q).' given');
        // if($v instanceof namespace\DomainNameLabels)throw new \Exception('not implemented yet...');
        // else
         // {
            // $v = explode('.', "$v");# Вызов explode('.', '') вернёт такой массив: array(1) { [0]=> string(0) "" }
            // foreach($v as $j) self::Check($j);
         // }
        // if(null === $i)
         // {
            // array_unshift($this->value, ...$v);
            // return;
         // }
        // elseif(is_int($i))
         // {
            // $j = $this->ConvertOffset($i);
            // if(isset($this->value[$j]))
             // {throw new \Exception('not implemented yet...');
                // if(count($v) > 1) array_splice($this->value, $j, 1, $v);
                // else $this->value[$j] = $v[0];
                // return;
             // }
            // else list($e, $m, $c) = E\UndefinedIndex($i);
         // }
        // else list($e, $m, $c) = E\InvalidIndex($i);
        // throw new $e($m, $c);
     // }

    final public function offsetSet($i, $v)
    {
        if (null === $i) {
            array_unshift($this->value, $v);
            return;
        } elseif (is_int($i)) {
            $j = $this->ConvertOffset($i);
            if (isset($this->value[$j])) {
                $this->value[$j] = $v;
                return;
            } else {
                list($e, $m, $c) = E\UndefinedIndex($i);
            }
        } else {
            list($e, $m, $c) = E\InvalidIndex($i);
        }
        throw new $e($m, $c);
    }

    final public function offsetUnset($i)
    {
        if (is_int($i)) {
            $j = $this->ConvertOffset($i);
            if (isset($this->value[$j])) {
                if (0 === $j) {
                    array_shift($this->value);# Удаление последнего элемента: индекс равен count() или -1; например, для www.example.com будет удалено www.
                } else {
                    throw new \Exception('not implemented yet...');//Пока не решено, что делать, поскольку решение "в лоб" не подходит: нет смысла в удалении example или com из www.example.com.
                }
            }
        } else {
            list($e, $m, $c) = E\InvalidIndex($i);
            throw new $e($m, $c);
        }
    }

    final public function Exists(int $i, int &$j = null): bool
    {
        $j = $this->ConvertOffset($i);// $j должно быть равно чему? по идее, индексу от 1 (внешнему), а не от 0 (внутреннему).
        if (isset($this->value[$j])) {
            throw new \Exception('not implemented yet...');
        } else {
            throw new \Exception('not implemented yet...');
        }
    }

    final public function ToArray(): array
    {
        $r = [];
        $cnt = count($this->value);
        for ($i = $cnt; $i > 0; --$i) {
            $r[$i] = $this->value[$cnt - $i];
        }
        return $r;
    }

    final public function __debugInfo(): array
    {
        return $this->ToArray();
    }
    final public function offsetExists($i)
    {
        return is_int($i) ? isset($this->value[$this->ConvertOffset($i)]) : false;
    }
    final public function __toString()
    {
        return implode('.', $this->value);
    }
    final public function count()
    {
        return count($this->value);
    }

    final protected function ConvertOffset(int $k): int
    {
        return $k < 0 ? -1 - $k : count($this->value) - $k;
    }
}
