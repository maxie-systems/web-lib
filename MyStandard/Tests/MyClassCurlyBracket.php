<?php

namespace MaxieSystems\Package {

    class MyClassCurlyBracket
    {
        public function hello()
        {
            echo namespace\MyClass::THE_CONST;
        }

        public const THE_CONST = 2;
    }

}
