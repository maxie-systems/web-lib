<?php

namespace MaxieSystems\HTTP;

class Headers implements \Iterator, \Countable, \ArrayAccess, \JsonSerializable
{
    public function __construct(iterable $headers)
    {
        foreach ($headers as $v) {
            if ($v instanceof namespace\Header) {
                $v = clone $v;
            } else {
                $v = Header::FromString($v);
                if (null === $v) {
                    continue;
                }
            }
            if ('' === $v->name) {
                if (isset($this->headers[$v->name])) {# При использовании прокси может быть дополнительный заголовок HTTP/1.1 200 Connection established
                    if ($this->headers[$v->name] instanceof namespace\MultipleHeader) {
                        $this->headers[$v->name][] = $v;
                        continue;
                    } else {
                        $v = new MultipleHeader($this->headers[$v->name], $v);
                    }
                }
            } else {
                if (isset($this->headers[$v->lc_name])) {# https://tools.ietf.org/html/rfc7230#section-3.2.2
                 // if(!Header::IsMultiple($v->lc_name)) throw new \UnexpectedValueException('Multiple headers are not allowed: '.$v->name);
                    if ($this->headers[$v->lc_name] instanceof namespace\MultipleHeader) {
                        $this->headers[$v->lc_name][] = $v;
                        continue;
                    } elseif ($this->headers[$v->lc_name]->value === $v->value) {
                        continue;
                    } else {
                        $v = new MultipleHeader($this->headers[$v->lc_name], $v);
                    }
                }
                $this->index[$v->lc_name] = $v;
                if ($v->_name !== $v->lc_name) {
                    $this->index[$v->_name] = $v;
                }
            }
            $this->headers[$v->lc_name] = $v;
        }//var_dump($this->headers);
    }

    final public function __get(string $name)
    {
        if ('' === $name) {
            if (isset($this->headers[$name])) {
                return $this->headers[$name]->value;
            }
        } else {
            if (isset($this->index[$name])) {
                return $this->index[$name]->value;
            }
            list($name, $_name, $lc_name) = Header::GetIndexNames($name);
            if (isset($this->headers[$lc_name])) {
                return $this->headers[$lc_name]->value;
            } else {
                $this->index[$_name] = $this->index[$lc_name] = (object)['value' => null];
            }
        }
        return null;
    }

    final public function offsetGet($name)
    {
        return $this->__get($name);
    }

    final public function Send(callable $filter = null): array
    {
        $ret_val = [];
        $send = function (string $hdr, bool $replace) use (&$ret_val) {
            header($hdr, $replace);
            $ret_val[] = $hdr;
        };
        if (null === $filter) {
            foreach ($this as $h) {
                if ($h instanceof namespace\MultipleHeader) {
                    foreach ($h as $v) {
                        $send($v, false);
                    }
                } else {
                    $send($h, true);
                }
            }
        } else {
            foreach ($this as $h) {
                if ($h instanceof namespace\MultipleHeader) {
                    foreach ($h as $v) {
                        if ($this->MkHdr($filter, $v, $hdr)) {
                            $send($hdr, false);
                        }
                    }
                } elseif ($this->MkHdr($filter, $h, $hdr)) {
                    $send($hdr, true);
                }
            }
        }
        return $ret_val;
    }

    final public function Merge(iterable ...$args): Headers
    {
        $r = clone $this;
        foreach ($args as $hdrs) {
            $tmp = true;
            if ($hdrs instanceof namespace\Headers) {
                $tmp = false;
            } else {
                $hdrs = new Headers($hdrs);
            }
            foreach ($hdrs as $h) {
                if (is_string($h)) {
                    $h = Header::FromString($h);
                    if (null === $h) {
                        continue;
                    }
                    $r->SetHeader($h);
                } elseif ($h instanceof namespace\IHeader) {
                    $r->SetHeader($tmp ? $h : clone $h);
                } else {
                    throw new \TypeError('Invalid argument type: ' . MS\GetVarType($h));
                }
            }
        }
        return $r;
    }

    final public function ToArray(callable $filter = null, ...$args): array
    {
        if (!$this->headers) {
            return [];
        }
        $ret_val = [];
        $add = function (string $hdr) use (&$ret_val) {
            $ret_val[] = $hdr;
        };
        if (null === $filter) {
            foreach ($this as $v) {
                if ($v instanceof namespace\MultipleHeader) {
                    foreach ($v as $hdr) {
                        $add($hdr);
                    }
                } else {
                    $add($v);
                }
            }
        } else {
            foreach ($this as $v) {
                if ($v instanceof namespace\MultipleHeader) {
                    foreach ($v as $h) {
                        if ($this->MkHdr($filter, $h, $hdr, ...$args)) {
                            $add($hdr);
                        }
                    }
                } elseif ($this->MkHdr($filter, $v, $hdr, ...$args)) {
                    $add($hdr);
                }
            }
        }
        return $ret_val;
    }

    final public function MergeArray(iterable ...$args): array
    {
        return $this->Merge(...$args)->ToArray();
    }
    final public function count()
    {
        return count($this->headers);
    }
    final public function rewind()
    {
        reset($this->headers);
    }
    final public function current()
    {
        if (false !== ($v = current($this->headers))) {
            return $v;
        }
    }
    final public function key()
    {
        if (false !== ($v = current($this->headers))) {
            return $v->name;
        }
    }
    final public function next()
    {
        next($this->headers);
    }
    final public function valid()
    {
        return null !== key($this->headers);
    }

    final public function __set(string $name, $value)
    {
        if (null === $value) {
            $this->__unset($name);
        } elseif (is_array($value)) {
            throw new \Exception('Not implemented yet...');// установить 3 отдельных заголовка DAV в уже созданном объекте Headers
        } else {
            if ('' !== $name) {
                $name = str_replace('_', '-', $name);
            }
            $this->Index(new Header($name, $value));
        }
    }

    final public function offsetSet($name, $value)
    {
        if (null === $name) {
            throw new \Exception('Not implemented yet...');
        } elseif (null === $value) {
            $this->offsetUnset($name);
        } elseif (is_array($value)) {
            throw new \Exception('Not implemented yet...');// установить 3 отдельных заголовка DAV в уже созданном объекте Headers
        } else {
            $this->Index(new Header($name, $value));
        }
    }

    final public function offsetExists($name)
    {
        return $this->__isset($name);
    }

    final public function offsetUnset($name)
    {
        if ('' === $name) {
            unset($this->headers[$name]);
        } else {
            list($name, $_name, $lc_name) = Header::GetIndexNames($name);
            if (isset($this->headers[$lc_name])) {
                unset($this->headers[$lc_name]);
                foreach (['_name', 'lc_name'] as $k) {
                    unset($this->index[$$k]);
                }
            }
        }
    }

    final public function __unset(string $name)
    {
        if ('' !== $name) {
            $name = str_replace('_', '-', $name);
        }
        $this->offsetUnset($name);
    }

    final public function __isset(string $n)
    {
        if (isset($this->index[$n])) {
            return true;
        }
        list($name, $_name, $lc_name) = Header::GetIndexNames($n);
        return isset($this->headers[$lc_name]);
    }

    final public function SetHeader(IHeader $h)
    {
        $this->Index($h);
    }

    final public function jsonSerialize()
    {
        return $this->ToArray();
    }
    final public function __debugInfo(): array
    {
        return $this->ToArray();
    }
    final public function __toString()
    {
        return implode("\r\n", $this->ToArray());
    }

    final public function __clone()
    {
        foreach ($this->headers as $k => $v) {
            $this->Index(clone $v);
        }
    }

    private function Index(IHeader $h)
    {
        $this->headers[$h->lc_name] = $h;
        if ('' !== $h->name) {
            foreach (['_name', 'lc_name'] as $k) {
                $this->index[$h->$k] = $h;
            }
        }
    }

    private function MkHdr(callable $f, Header $v, string &$hdr = null, ...$args): bool
    {
        $r = $f($v, ...$args);
        if (true === $r) {
            $hdr = "$v";
            return true;
        } elseif (null === $r || false === $r) {
            $hdr = null;
            return false;
        } else {
            $hdr = "$r";
            if ('' !== $v->name) {
                $hdr = "$v->name: $hdr";
            }
            return true;
        }
    }

    private $headers = [];
    private $index = [];
}
