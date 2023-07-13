<?php

namespace MaxieSystems;

class URL
{
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
