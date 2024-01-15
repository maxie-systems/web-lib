<?php

function helloAgain()
{
    echo namespace\MyClassNoNS::THE_CONST;
}

class MyClassNoNS
{
    public function hello()
    {
        echo namespace\MyClassNoNS::THE_CONST;
    }

    public const THE_CONST = 5;
}
