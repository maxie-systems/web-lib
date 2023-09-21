<?php

namespace MaxieSystems\URL;

use MaxieSystems\Exception\Messages as EMsg;

class DomainName extends Host implements \ArrayAccess, \Countable
{
    final public function __construct(string $value)
    {
        if (!self::isDomainName($value)) {
            throw new \MaxieSystems\Exception\URL\InvalidDomainNameException();
        }
        $this->labels = new DomainName\Labels($value);
    }

    private readonly DomainName\Labels $labels;

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
    # $a <=> $b	Spaceship	An int less than, equal to, or greater than zero when $a is less than, equal to, or greater than $b, respectively.
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

    final public function __clone()
    {
        $this->labels = clone $this->labels;
    }

    final public function offsetSet($n, $v): void
    {
        throw new \Error(EMsg::readonlyObject($this));
    }

    final public function offsetUnset($n): void
    {
        throw new \Error(EMsg::readonlyObject($this));
    }

    #[\ReturnTypeWillChange]
    final public function offsetGet($n): ?string
    {
        return $this->labels->offsetGet($n);
    }

    final public function offsetExists($n): bool
    {
        return $this->labels->offsetExists($n);
    }

    final public function count(): int
    {
        return count($this->labels);
    }

    final public function __toString()
    {
        return $this->labels->__toString();
    }

    final public function __debugInfo(): array
    {
        return $this->labels->__debugInfo();
    }
}
