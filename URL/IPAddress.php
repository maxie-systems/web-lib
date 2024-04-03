<?php

namespace MaxieSystems\URL;

/**
 * @property-read string $value
 * @property-read bool $v6
 */
class IPAddress extends Host
{
    public readonly string $value;
    public readonly bool $v6;

    public function __construct(string $value)
    {
        if (!self::IsIP($value, $is_v6, $v)) {
            throw new Exception\InvalidIPAddressException();
        }
        $this->v6 = $is_v6;
        $this->value = $v;
    }

    final public function __toString()
    {
        return $this->v6 ? "[$this->value]" : $this->value;
    }

    final public function __debugInfo(): array
    {
        return ['value' => $this->value, 'v6' => $this->v6];
    }
}
