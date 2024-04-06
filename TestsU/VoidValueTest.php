<?php

declare(strict_types=1);

namespace MaxieSystems\Tests;

use MaxieSystems\VoidValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(VoidValue::class)]
final class VoidValueTest extends TestCase
{
    public function testToString(): void
    {
        $this->assertSame('', (string)new VoidValue());
    }
}
