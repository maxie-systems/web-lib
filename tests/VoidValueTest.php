<?php

declare(strict_types=1);

namespace MaxieSystems;

use PHPUnit\Framework\TestCase;

final class VoidValueTest extends TestCase
{
    public function testToString(): void
    {
        $this->assertSame('', (string)new VoidValue());
    }
}
