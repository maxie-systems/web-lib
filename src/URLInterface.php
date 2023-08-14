<?php

namespace MaxieSystems;

interface URLInterface extends \Iterator, \ArrayAccess
{
    public function isAbsolute(URLType &$type = null): bool;
    public function getType(): URLType;
    public function isEmpty(string $group = null): bool;
    public function __unset($name): void;
    public function __set($name, $value): void;
    public function __isset($name): bool;
    public function __get($name): mixed;
    public function __toString();
    public function __debugInfo(): array;
    public function __clone();
    public function toStdClass(callable $callback = null, ...$args): \stdClass;
    public function toArray(callable $callback = null, ...$args): array;
}
