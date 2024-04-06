<?php

namespace MaxieSystems\HTTP;

interface HeaderValueInterface
{
    public function __toString(): string;
    public function __debugInfo(): array;
}
