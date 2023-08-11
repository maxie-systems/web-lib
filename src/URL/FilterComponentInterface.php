<?php

namespace MaxieSystems\URL;

use ArrayAccess;
use Stringable;

interface FilterComponentInterface
{
    public function __invoke(
        string $name,
        mixed $value,
        array|ArrayAccess $src_url,
        ...$args
    ): string|Stringable|int|null;
}
