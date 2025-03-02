<?php

namespace MaxieSystems\HTTP\HeaderValue;

class ContentType implements \MaxieSystems\HTTP\HeaderValueInterface
{
    final public function __construct(string $content_type)#: array
    {
        $r = ['', ''];
        if ($content_type) {
            $a = explode(';', $content_type, 2);
            $r[0] = strtolower($a[0]);
            if (!empty($a[1])) {
                $s = 'charset=';
                if (false !== ($pos = strpos($a[1], $s))) {
                    $r[1] = strtolower(trim(substr($a[1], $pos + strlen($s)), ' \'"'));
                }
            }
        }
        //return $r;
    }
}
