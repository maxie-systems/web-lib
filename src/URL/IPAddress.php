<?php

namespace MaxieSystems\URL;

class IPAddress extends Host
{
    public function __construct(private readonly string $value)
    {
        if (!self::IsIP($value)) {
            throw new \ValueError('Invalid IP address');
        }
    }

    final public function __toString()
    {
        return $this->value;
    }

    final public function __debugInfo(): array
    {
        return ['value' => $this->value, ];
    }
}
