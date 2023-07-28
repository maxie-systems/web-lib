<?php

namespace MaxieSystems\URL;

abstract class Host
{
    final public static function IsIP(string $v): bool
    {
        return (bool)filter_var($v, FILTER_VALIDATE_IP);
    }

    final public static function Create(string $v, ...$args): Host
    {
        try {
            return new IPAddress($v, ...$args);
        } catch (\ValueError $e) {
            return new DomainName($v, ...$args);
        }
    }
}
