<?php

namespace MaxieSystems\URL;

class DomainName extends Host implements \ArrayAccess, \Countable
{
    final public function __construct(string $value)
    {
        if (!self::isDomainName($value)) {
            throw new \MaxieSystems\Exception\URL\InvalidDomainNameException();
        }
        //$this->value = new DomainName\Labels($value);//self::Encode($value));
    }

    # https://www.php.net/manual/en/function.strcmp
    # strcmp(string $string1, string $string2): int
    # Returns < 0 if string1 is less than string2; > 0 if string1 is greater than string2, and 0 if they are equal.
    # https://www.php.net/manual/en/collator.compare
    # Collator::Compare(string $string1, string $string2): int|false
    # Return comparison result:
    # 1 if string1 is greater than string2 ;
    # 0 if string1 is equal to string2;
    # -1 if string1 is less than string2 .
    # Returns false on failure.
    final public function compare(string $domain, ?string &$label): int|false
    {
        $label = null;
        $domain = new DomainName\Labels($domain);
        $c0 = count($this);
        $c1 = count($domain);
        if ($c0 === $c1) {
            if ($this->__toString() === $domain->__toString()) {
                $label = '';
                return 0;
            } else {
                return false;
            }
        }
        if ($c0 > $c1) {
            $c = $c1;
            $c1 = $c0;
            $c0 = $c;
            $c = $this;
            $j = 1;
        } else {
            $c = $domain;
            $j = -1;
        }
        for ($i = 1; $i <= $c0; ++$i) {
            if ($domain[$i] !== $this[$i]) {
                return false;
            }
        }
        $label = '';
        for (; $i <= $c1; ++$i) {
            $label = ($i < $c1 ? '.' : '') . $c[$i] . $label;
        }
        return $j;
    }

    final public function PushLabel(string $label, string ...$replace): string
    {
        $len = count($this);
        if (!$len) {
            throw new \Exception('not implemented yet...');
        }
        if ('' === $label) {
            if ($replace) {
                $labels = clone $this->value;
                $last = $labels[-1];
                foreach ($replace as $j) {
                    if ($j === $last) {
                        unset($labels[-1]);
                        break;
                    }
                }
            } else {
                list($e, $m, $c) = E\TooFewArguments(__CLASS__, __FUNCTION__);
                throw new $e($m, $c);
            }
        } else {
            for ($j = $len, $last = ''; $j > 0; --$j) {
                if ('' !== $last) {
                    $last .= '.';
                }
                $last .= $this->value[$j];
                if ($label === $last) {
                    return $this->value->__toString();# Это означает, что домен уже начинается с указанной метки.
                }
            }
            $labels = clone $this->value;
            if ($replace) {
                $last = $labels[-1];
                foreach ($replace as $j) {
                    if ($j === $last) {
                        $labels[-1] = $label;# $label может содержать точки, но поскольку $labels - временный объект, который тут же преобразуется в строку и больше не используется, то не заморачиваемся.
                        return $labels->__toString();
                    }
                }
            }
            $labels[] = $label;
        }
        return $labels->__toString();
    }

    // final public function ToggleSubdomain(string $label) : bool
     // {
        // $this->Modify();
        // $f = \MaxieSystems\URL::ToggleSubdomain($this->value, $label, $this->value);
        // $this->value_decoded = null;// !!! проверить преобразование в строку!!!
        // return $f;
     // }

    final public function __get($n)
    {
        if ('decoded' === $n) {
            return $this->value ? self::Decode($this->value) : $this->value;
        }
        list($e, $m, $c) = E\UndefinedProperty($this, $n);
        throw new $e($m, $c);
    }

    final public function __clone()
    {
        $this->value = clone $this->value;
    }

    final public function offsetSet($n, $v): void
    {
        throw new \Exception('not implemented yet...');
        $this->value->offsetSet($n, $v);
    }
    final public function offsetUnset($n): void
    {
        $this->value->offsetUnset($n);
    }
    // #[\ReturnTypeWillChange] string ???
    final public function offsetGet($n): mixed
    {
        return $this->value->offsetGet($n);
    }
    final public function offsetExists($n): bool
    {
        return $this->value->offsetExists($n);
    }
    final public function count(): int
    {
        return count($this->value);
    }
    final public function __toString()
    {
        return $this->value->__toString();
    }
    final public function __debugInfo(): array
    {
        return ['encoded' => $this->value->__toString(), 'decoded' => $this->__get('decoded')];
    }

    final public static function Encode(string $domain): string
    {
        if (!isset(self::$idna_val_enc[$domain])) {
            if (null === self::$idna) {
                self::$idna = new \idna_convert();
            }
            $v = self::$idna->encode($domain);
            self::$idna_val_enc[$domain] = self::$idna_val_enc[$v] = $v;
        }
        return self::$idna_val_enc[$domain];
    }

    final public static function Decode(string $domain): string
    {
        if (!isset(self::$idna_val_dec[$domain])) {
            if (null === self::$idna) {
                self::$idna = new \idna_convert();
            }
            $v = self::$idna->decode($domain);
            self::$idna_val_dec[$domain] = self::$idna_val_dec[$v] = $v;
        }
        return self::$idna_val_dec[$domain];
    }

    private static $idna = null;
    private static $idna_val_enc = [];
    private static $idna_val_dec = [];
}
