<?php

namespace MaxieSystems\Package;

abstract class MyClass implements MyInterface
{
    public function hello()
    {
        echo namespace\MyClass::THE_CONST;
    }

    public const THE_CONST = 2;
}
