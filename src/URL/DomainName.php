<?php

namespace MaxieSystems\URL;

use MaxieSystems\Exception\Messages as EMsg;

class DomainName extends Host implements \ArrayAccess, \Countable
{
    final public function __construct(string $value)
    {
        if (!self::isDomainName($value)) {
            throw new Exception\InvalidDomainNameException();
        }
        $this->labels = new DomainName\Labels($value);
    }

    /**
     * Returns comparison result:
     * 1 if this value is subdomain of $domain,
     * 0 if this value is equal to $domain,
     * -1 if $domain is subdomain of this value,
     * false otherwise.
     */
    final public function compare(string|self $domain, ?string &$label): int|false
    {
        $label = null;
        if (is_string($domain)) {
            $domain = new DomainName\Labels($domain);
        }
        $c0 = count($this);
        $c1 = count($domain);
        if ($c0 === $c1) {
            if ($this->__toString() === $domain->__toString()) {
                $label = '';
                return 0;
            } else {
                return false;
            }
        } elseif ($c0 > $c1) {
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

    private readonly DomainName\Labels $labels;
}
