<?php

namespace MaxieSystems\Package {

    function testIt(): bool
    {
        return true;
    }

}

namespace {

    final readonly class MyClassDoubleNS
    {
        public function hello()
        {
            echo namespace\MyClassDoubleNS::THE_CONST;
        }

        protected const THE_CONST = 22;
    }

}
