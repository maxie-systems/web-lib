<?php

namespace MaxieSystems\URL;

abstract class Host
{
    final public static function isIP(string $v, bool &$is_v6 = null, string &$value = null): bool
    {
        if ('' === $v) {
            $value = $is_v6 = null;
            return false;
        } elseif ($value = filter_var($v, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_NULL_ON_FAILURE)) {
            $is_v6 = false;
            return true;
        }
        if ('[' === $v[0] && ']' === $v[strlen($v) - 1]) {
            $v = substr($v, 1, -1);
        }
        if ($value = filter_var($v, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | FILTER_NULL_ON_FAILURE)) {
            $is_ip = $is_v6 = true;
        } else {
            $is_v6 = null;
            $is_ip = false;
        }
        return $is_ip;
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
        } catch (Exception\InvalidIPAddressException $e) {
            try {
                return new DomainName($v);
            } catch (Exception\InvalidDomainNameException $e) {
                throw new Exception\InvalidHostException();
            }
        }
    }

    abstract public function __debugInfo(): array;
    abstract public function __toString();
}
