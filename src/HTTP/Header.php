<?php

namespace MaxieSystems\HTTP;

use MaxieSystems\Exception\HTTP\EmptyHeaderNameException;

/**
 * @property-read string $name
 * @property-read string $name_lc
 * @property-read string|HeaderValueInterface $value
 */
class Header
{
    final public function __construct(string $header, string|HeaderValueInterface $value = null)
    {
        if (null === $value) {
            $header = explode(':', $header, 2);
            if (2 === count($header)) {
                $value = ltrim($header[1]);
                $header = $header[0];
            } else {
                throw new EmptyHeaderNameException();
            }
        }
        if ('' === $header) {
            throw new EmptyHeaderNameException();
        }
        $this->name_lc = strtolower($header);
        $this->name = ucwords($header, '-');
        $this->value = $value;
    }


    final public function __toString(): string
    {
        return $this->name . ': ' . $this->value;
    }

    final public function __debugInfo(): array
    {
        return ['header' => $this->__toString()];
    }

    public readonly string $name;
    public readonly string $name_lc;
    private readonly string|HeaderValueInterface $value;
}
