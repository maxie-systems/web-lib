<?php

namespace MaxieSystems;

class URL
{
    final public static function build(object $url): string
    {
        $s = '';
        if ('' !== $url->scheme) {
            $s .= "$url->scheme:";
        }
        $h = "$url->host";
        if ('' !== $h) {
            $s .= '//';
            if ('' !== $url->user) {
                $s .= $url->user;
                if ($url->pass) {
                    $s .= ":$url->pass";
                }
                $s .= '@';
            }
            $s .= $h;
            if ($url->port) {
                $s .= ":$url->port";
            }
        }
        $s .= $url->path;
        $q = "$url->query";
        if ('' !== $q) {
            $s .= "?$q";
        }
        if ('' !== $url->fragment) {
            $s .= "#$url->fragment";
        }
        return $s;
    }

    final public static function encode(string $string): string
    {
        static $s = [
            '%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D',
            '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D'
        ];
        static $r = [
            '!',   '*',   "'",   "(",   ")",   ";",   ":",   "@",   "&",   "=",
            "+",   "$",   ",",   "/",   "?",   "%",   "#",   "[",   "]"
        ];
        return str_replace($s, $r, rawurlencode($string));
    }
}
