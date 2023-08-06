<?php

namespace MaxieSystems\URL;

use MaxieSystems\Exception\URL\InvalidIPAddressException;
use MaxieSystems\Exception\URL\InvalidDomainNameException;
use MaxieSystems\Exception\URL\InvalidHostException;

abstract class Host
{
    final public static function isIP(string $v, bool &$is_v6 = null, string &$value = null): bool
    {
        if ($value = filter_var($v, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_NULL_ON_FAILURE)) {
            $is_v6 = false;
            return true;
        }
        if ('[' === $v[0] && ']' === $v[strlen($v) - 1]) {
            $v = substr($v, 1, -1);
        }
        if ($value = filter_var($v, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | FILTER_NULL_ON_FAILURE)) {
            $is_v6 = true;
            return true;
        }
        $is_v6 = null;
        return false;
    }

    final public static function isDomainName(string $v): bool
    {
        return filter_var($v, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)
            && !filter_var($v, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    final public static function create(string $v): Host
    {
        try {
            return new IPAddress($v);
        } catch (InvalidIPAddressException $e) {
            try {
                return new DomainName($v);
            } catch (InvalidDomainNameException $e) {
                throw new InvalidHostException();
            }
        }
    }

    abstract public function __debugInfo(): array;
    abstract public function __toString();
}
