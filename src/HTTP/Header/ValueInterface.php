<?php

namespace MaxieSystems\HTTP\Header;

interface ValueInterface
{
    public function __toString(): string;
    public function __debugInfo(): array;
}
