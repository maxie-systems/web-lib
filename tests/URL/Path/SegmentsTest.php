<?php

declare(strict_types=1);

namespace MaxieSystems\URL\Path;

use PHPUnit\Framework\TestCase;

final class SegmentsTest extends TestCase
{
    public function testFilterSegment(): void
    {
        $filter = function (string $segment, int $i): ?string {
            if ('' === $segment) {
                return null;
            }
            return strtolower($segment);
        };
        $segments = new Segments('/My/CaseSensitive/PathName/index.php', $filter);
        $this->assertSame('pathname', $segments[2]);
    }

    public function testOffsetGet(): void
    {
        $segments = new Segments('/my/pathname/index.php');
        $this->assertSame('pathname', $segments[1]);
    }

    public function testOffsetExists(): void
    {
        $segments = new Segments('/my/pathname/index.php');
        $this->assertTrue(isset($segments[0]));
        $this->assertNotTrue(isset($segments[10]));
        $this->assertTrue(isset($segments[-1]));
        $this->assertNotTrue(isset($segments[-11]));
    }

    public function testOffsetSet(): void
    {
        $segments = new Segments('/my/pathname/index.php');
        $this->expectException(\Error::class);
        $segments[0] = 'xxx';
    }

    public function testOffsetUnset(): void
    {
        $segments = new Segments('/my/pathname/index.php');
        $this->expectException(\Error::class);
        unset($segments[0]);
    }
}
