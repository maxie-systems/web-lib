<?php

namespace  MaxieSystems\Package  ;

class MyClassSpaces
{
    public function hello()
    {
        echo namespace\MyClassSpaces::THE_CONST;
    }

    protected const THE_CONST = 2;
}
